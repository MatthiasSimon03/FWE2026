<?php

namespace App\Controllers\FlightMeet;

use App\Models\FlightMeet\GroupModel;
use App\Models\FlightMeet\GroupMemberModel;
use App\Models\FlightMeet\GroupJoinRequestModel;

class GroupController extends BaseController
{
    protected $groupModel;
    protected $memberModel;
    protected $requestModel;

    public function __construct()
    {
        $this->groupModel   = new GroupModel();
        $this->memberModel  = new GroupMemberModel();
        $this->requestModel = new GroupJoinRequestModel();
    }

    public function index()
    {
        $search = $this->request->getGet('q') ?? '';
        $groups = $this->groupModel->getGroups($search);
        $userId = session()->get('fm_user_id');

        // Für jede Gruppe prüfen, ob der User Mitglied ist
        foreach ($groups as &$group) {
            $group['is_member'] = $this->memberModel->isMember($group['id'], $userId);
            $group['user_role'] = $this->memberModel->getUserRole($group['id'], $userId);
            $group['has_pending'] = $this->requestModel->hasPendingRequest($group['id'], $userId);
        }

        return view('FlightMeet/groups/index', [
            'title'  => 'Gruppenübersicht',
            'active' => 'groups',
            'groups' => $groups,
            'search' => $search
        ]);
    }

    public function detail(int $id)
    {
        $group = $this->groupModel->getGroupById($id);
        if (!$group) {
            return redirect()->to('flightmeet/groups')->with('error', 'Gruppe wurde nicht gefunden.');
        }

        $userId = session()->get('fm_user_id');
        $isMember = $this->memberModel->isMember($id, $userId);
        $userRole = $this->memberModel->getUserRole($id, $userId);

        // Sicherheitsprüfung für private Gruppen
        if ($group['visibility'] === 'private' && !$isMember) {
            $hasPending = $this->requestModel->hasPendingRequest($id, $userId);
            return view('FlightMeet/groups/detail_private', [
                'title'       => esc($group['name']),
                'active'      => 'groups',
                'group'       => $group,
                'has_pending' => $hasPending
            ]);
        }

        // Flüge holen (geplant vs. historisch)
        $scheduledFlights = $this->groupModel->getGroupMeetups($id, ['geplant', 'ausgebucht']);
        $historicFlights  = $this->groupModel->getGroupMeetups($id, ['abgeschlossen', 'abgesagt']);

        // Mitgliederliste
        $members = $this->memberModel->getMembersWithDetails($id);

        // Beitrittsanfragen holen (nur für Admins & Owners)
        $pendingRequests = [];
        if (in_array($userRole, ['owner', 'admin'], true)) {
            $pendingRequests = $this->requestModel->getPendingRequests($id);
        }

        return view('FlightMeet/groups/detail', [
            'title'             => esc($group['name']),
            'active'            => 'groups',
            'group'             => $group,
            'is_member'         => $isMember,
            'user_role'         => $userRole,
            'scheduled_flights' => $scheduledFlights,
            'historic_flights'  => $historicFlights,
            'members'           => $members,
            'pending_requests'  => $pendingRequests
        ]);
    }

    public function create()
    {
        if ($this->request->is('post')) {
            $data = [
                'name'          => $this->request->getPost('name'),
                'description'   => $this->request->getPost('description'),
                'rules'         => $this->request->getPost('rules'),
                'visibility'    => $this->request->getPost('visibility'),
                'region'        => $this->request->getPost('region'),
                'base_location' => $this->request->getPost('base_location'),
                'latitude'      => $this->request->getPost('latitude') ?: null,
                'longitude'     => $this->request->getPost('longitude') ?: null,
                'created_by'    => session()->get('fm_user_id')
            ];

            // Validierung: Name muss eindeutig sein
            if ($this->groupModel->where('name', $data['name'])->countAllResults() > 0) {
                return redirect()->back()->withInput()->with('error', 'Eine Gruppe mit diesem Namen existiert bereits.');
            }

            $this->groupModel->db->transStart();

            $groupId = $this->groupModel->insert($data);
            if ($groupId) {
                // Ersteller wird automatisch Owner
                $this->memberModel->addMember($groupId, $data['created_by'], 'owner');
            }

            $this->groupModel->db->transComplete();

            if ($this->groupModel->db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Fehler beim Erstellen der Gruppe.');
            }

            return redirect()->to('flightmeet/groups/detail/' . $groupId)->with('success', 'Gruppe erfolgreich erstellt!');
        }

        return view('FlightMeet/groups/create', [
            'title'  => 'Gruppe gründen',
            'active' => 'groups'
        ]);
    }

    public function edit(int $id)
    {
        $group = $this->groupModel->find($id);
        if (!$group) {
            return redirect()->to('flightmeet/groups')->with('error', 'Gruppe nicht gefunden.');
        }

        $userId = session()->get('fm_user_id');
        $userRole = $this->memberModel->getUserRole($id, $userId);

        if ($userRole !== 'owner') {
            return redirect()->to('flightmeet/groups/detail/' . $id)->with('error', 'Nur der Besitzer darf die Gruppe bearbeiten.');
        }

        if ($this->request->is('post')) {
            $data = [
                'description'   => $this->request->getPost('description'),
                'rules'         => $this->request->getPost('rules'),
                'visibility'    => $this->request->getPost('visibility'),
                'region'        => $this->request->getPost('region'),
                'base_location' => $this->request->getPost('base_location'),
                'latitude'      => $this->request->getPost('latitude') ?: null,
                'longitude'     => $this->request->getPost('longitude') ?: null,
            ];

            if ($this->groupModel->update($id, $data)) {
                return redirect()->to('flightmeet/groups/detail/' . $id)->with('success', 'Gruppe erfolgreich aktualisiert.');
            }
            return redirect()->back()->withInput()->with('error', 'Fehler beim Aktualisieren der Gruppe.');
        }

        return view('FlightMeet/groups/edit', [
            'title'  => 'Gruppe bearbeiten',
            'active' => 'groups',
            'group'  => $group
        ]);
    }

    public function delete(int $id)
    {
        $group = $this->groupModel->find($id);
        if (!$group) {
            return redirect()->to('flightmeet/groups')->with('error', 'Gruppe nicht gefunden.');
        }

        if ((int)$group['created_by'] !== (int)session()->get('fm_user_id')) {
            return redirect()->to('flightmeet/groups/detail/' . $id)->with('error', 'Nur der Besitzer darf diese Gruppe löschen.');
        }

        if ($this->groupModel->delete($id)) {
            return redirect()->to('flightmeet/groups')->with('success', 'Gruppe erfolgreich gelöscht.');
        }
        return redirect()->to('flightmeet/groups/detail/' . $id)->with('error', 'Fehler beim Löschen.');
    }

    public function join(int $id)
    {
        $group = $this->groupModel->find($id);
        if (!$group || $group['visibility'] !== 'open') {   // separate Methode für private Gruppen (request join)
            return redirect()->to('flightmeet/groups')->with('error', 'Aktion nicht erlaubt.');
        }

        $userId = session()->get('fm_user_id');
        if ($this->memberModel->addMember($id, $userId, 'member')) {
            return redirect()->to('flightmeet/groups/detail/' . $id)->with('success', 'Du bist der Gruppe beigetreten!');
        }
        return redirect()->to('flightmeet/groups/detail/' . $id)->with('error', 'Beitritt fehlgeschlagen.');
    }

    public function leave(int $id)
    {
        $userId = session()->get('fm_user_id');
        $role = $this->memberModel->getUserRole($id, $userId);

        if ($role === 'owner') {
            return redirect()->to('flightmeet/groups/detail/' . $id)->with('error', 'Besitzer können die Gruppe nicht verlassen. Übertrage zuerst den Besitz.');
        }

        if ($this->memberModel->removeMember($id, $userId)) {
            return redirect()->to('flightmeet/groups')->with('success', 'Du hast die Gruppe verlassen.');
        }
        return redirect()->to('flightmeet/groups/detail/' . $id)->with('error', 'Aktion fehlgeschlagen.');
    }

    public function requestJoin(int $id)
    {
        $group = $this->groupModel->find($id);
        if (!$group || $group['visibility'] !== 'private') {    // Für offene Gruppen gibt es keine Beitrittsanfrage
            return redirect()->to('flightmeet/groups')->with('error', 'Ungültige Gruppe.');
        }

        $userId = session()->get('fm_user_id');

        if ($this->memberModel->isMember($id, $userId)) {
            return redirect()->to('flightmeet/groups/detail/' . $id)->with('error', 'Du bist bereits Mitglied.');
        }

        if ($this->requestModel->hasPendingRequest($id, $userId)) {
            return redirect()->back()->with('error', 'Du hast bereits eine ausstehende Anfrage für diese Gruppe.');
        }

        // Falls bereits eine abgelehnte oder stornierte Zeile existiert -> UPDATE, sonst INSERT
        $existing = $this->requestModel->where('group_id', $id)->where('user_id', $userId)->first();
        $message = $this->request->getPost('message') ?? '';

        if ($existing) {    // User hatte für diese Gruppe bereits eine Anfrage gesendet (diese wurde abgelehnt)
            $this->requestModel->update($existing['id'], [
                'status'       => 'pending',
                'message'      => $message,
                'requested_at' => date('Y-m-d H:i:s')
            ]);
        } else {        // User hat für diese Gruppe noch nie eine Anfrage gestellt
            $this->requestModel->insert([
                'group_id' => $id,
                'user_id'  => $userId,
                'status'   => 'pending',
                'message'  => $message
            ]);
        }

        return redirect()->to('flightmeet/groups')->with('success', 'Beitrittsanfrage wurde gesendet.');
    }

    public function approveRequest(int $requestId)
    {
        $request = $this->requestModel->find($requestId);
        if (!$request || $request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Anfrage nicht gefunden.');
        }

        $userId = session()->get('fm_user_id');
        $userRole = $this->memberModel->getUserRole($request['group_id'], $userId);

        if (!in_array($userRole, ['owner', 'admin'], true)) {
            return redirect()->back()->with('error', 'Keine Berechtigung.');
        }

        $this->requestModel->db->transStart();

        // 1. Anfrage auf accepted setzen
        $this->requestModel->update($requestId, [
            'status'     => 'accepted',
            'handled_by' => $userId,
            'handled_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Nutzer als Mitglied eintragen
        $this->memberModel->addMember($request['group_id'], $request['user_id'], 'member');

        $this->requestModel->db->transComplete();

        if ($this->requestModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Fehler beim Annehmen der Anfrage.');
        }

        return redirect()->back()->with('success', 'Beitrittsanfrage wurde angenommen.');
    }

    public function rejectRequest(int $requestId)
    {
        $request = $this->requestModel->find($requestId);
        if (!$request || $request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Anfrage nicht gefunden.');
        }

        $userId = session()->get('fm_user_id');
        $userRole = $this->memberModel->getUserRole($request['group_id'], $userId);

        if (!in_array($userRole, ['owner', 'admin'], true)) {
            return redirect()->back()->with('error', 'Keine Berechtigung.');
        }

        $this->requestModel->update($requestId, [
            'status'     => 'rejected',
            'handled_by' => $userId,
            'handled_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', 'Beitrittsanfrage wurde abgelehnt.');
    }

    public function promoteToAdmin(int $groupId, int $userId)
    {
        $currentUserId = session()->get('fm_user_id');
        $currentUserRole = $this->memberModel->getUserRole($groupId, $currentUserId);

        if (!in_array($currentUserRole, ['owner', 'admin'], true)) {
            return redirect()->back()->with('error', 'Keine Berechtigung.');
        }

        // Admins dürfen nur "members" zu "admins" befördern (nicht Rollen anderer Admins verändern)
        $targetRole = $this->memberModel->getUserRole($groupId, $userId);
        if ($currentUserRole === 'admin' && $targetRole !== 'member') {
            return redirect()->back()->with('error', 'Du kannst nur normale Mitglieder befördern.');
        }

        if ($this->memberModel->where('group_id', $groupId)->where('user_id', $userId)->set(['role' => 'admin'])->update()) {
            return redirect()->back()->with('success', 'Benutzer wurde zum Admin ernannt.');
        }
        return redirect()->back()->with('error', 'Aktion fehlgeschlagen.');
    }

    public function demoteFromAdmin(int $groupId, int $userId)
    {
        $currentUserId = session()->get('fm_user_id');
        $currentUserRole = $this->memberModel->getUserRole($groupId, $currentUserId);

        if ($currentUserRole !== 'owner') {
            return redirect()->back()->with('error', 'Nur der Besitzer darf Admins herabstufen.');
        }

        if ($this->memberModel->where('group_id', $groupId)->where('user_id', $userId)->set(['role' => 'member'])->update()) {
            return redirect()->back()->with('success', 'Admin wurde zum normalen Mitglied herabgestuft.');
        }
        return redirect()->back()->with('error', 'Aktion fehlgeschlagen.');
    }

    public function transferOwner(int $groupId, int $userId)
    {
        $currentUserId = session()->get('fm_user_id');
        $currentUserRole = $this->memberModel->getUserRole($groupId, $currentUserId);

        if ($currentUserRole !== 'owner') {
            return redirect()->back()->with('error', 'Nur der Besitzer kann die Inhaberschaft übertragen.');
        }

        $this->groupModel->db->transStart();

        // 1. Alten Owner auf Admin herabstufen
        $this->memberModel->where('group_id', $groupId)->where('user_id', $currentUserId)->set(['role' => 'admin'])->update();

        // 2. Neuen Owner setzen
        $this->memberModel->where('group_id', $groupId)->where('user_id', $userId)->set(['role' => 'owner'])->update();

        // 3. Spalte 'created_by' in fm_groups updaten
        $this->groupModel->update($groupId, ['created_by' => $userId]);

        $this->groupModel->db->transComplete();

        if ($this->groupModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Inhabertransfer fehlgeschlagen.');
        }

        return redirect()->to('flightmeet/groups/detail/' . $groupId)->with('success', 'Inhaberschaft erfolgreich übertragen. Du bist jetzt Admin.');
    }

    public function removeMember(int $groupId, int $userId)
    {
        $currentUserId = session()->get('fm_user_id');
        $currentUserRole = $this->memberModel->getUserRole($groupId, $currentUserId);

        if (!in_array($currentUserRole, ['owner', 'admin'], true)) {
            return redirect()->back()->with('error', 'Keine Berechtigung.');
        }

        $targetUserRole = $this->memberModel->getUserRole($groupId, $userId);

        // Sicherheitsprüfungen
        if ($userId === $currentUserId) {
            return redirect()->back()->with('error', 'Nutze "Gruppe verlassen", um dich selbst auszutragen.');
        }
        if ($targetUserRole === 'owner') {
            return redirect()->back()->with('error', 'Der Besitzer darf nicht entfernt werden.');
        }
        if ($currentUserRole === 'admin' && $targetUserRole === 'admin') {
            return redirect()->back()->with('error', 'Admins dürfen keine anderen Admins entfernen.');
        }

        if ($this->memberModel->removeMember($groupId, $userId)) {
            return redirect()->back()->with('success', 'Mitglied erfolgreich entfernt.');
        }
        return redirect()->back()->with('error', 'Entfernen fehlgeschlagen.');
    }
}