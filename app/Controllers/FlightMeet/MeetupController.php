<?php

namespace App\Controllers\FlightMeet;

use App\Models\FlightMeet\MeetupModel;
use CodeIgniter\HTTP\ResponseInterface;

class MeetupController extends BaseController
{
    // DB-Modell für das Erstellen, Verwalten und Filtern von Flugtreffen
    protected MeetupModel $meetupModel;

    public function __construct()
    {
        $this->meetupModel = new MeetupModel();
    }

    /**
     * Übersicht aller Flugtreffen mit Filteroptionen
     */
    public function index(): ResponseInterface
    {
        $allGetParams = $this->request->getGet();

        // Standardverhalten: Beim ersten Laden der Seite werden nur geplante Treffen angezeigt.
        // Sobald Filter aktiv gesendet werden, werden auch leere Checkbox-Auswahlen erlaubt.
        if (empty($allGetParams)) {
            $status = ['geplant'];
        } else {
            $statusParam = $this->request->getGet('status');
            if ($statusParam === null) {
                $status = [];
            } else {
                $status = is_array($statusParam) ? $statusParam : [];
            }
        }

        $filters = [
            'q'      => (string) ($this->request->getGet('q') ?? ''),
            'region' => (string) ($this->request->getGet('region') ?? ''),
            'level'  => (string) ($this->request->getGet('level') ?? ''),
            'status' => $status,
        ];

        // Dropdowns dynamisch mit Werten befüllen, die in der DB existieren
        $options = $this->meetupModel->getFilterOptions();

        return $this->response->setBody(view('FlightMeet/meetups', [
            'title'   => 'FlightMeet - Flugtreffen',
            'active'  => 'meetups',
            'meetups' => $this->meetupModel->getMeetups($filters),
            'filters' => $filters,
            'options' => $options,
        ]));
    }

    /**
     * Detailseite eines einzelnen Treffens
     */
    public function detail(int $id): ResponseInterface
    {
        $currentUserId = session()->get('fm_user_id');
        $meetup = $this->meetupModel->getMeetupById($id, $currentUserId);

        if ($meetup === null) {
            return redirect()
                ->to('/flightmeet/meetups')
                ->with('error', 'Flugtreffen wurde nicht gefunden.');
        }

        // Kontext-Tracking: Ermöglicht eine "Zurück zur Gruppe"-Navigation, falls von dort aufgerufen
        $fromGroup = $this->request->getGet('from_group');
        $meetup['from_group'] = $fromGroup ? (int)$fromGroup : null;

        return $this->response->setBody(view('FlightMeet/meetupDetail', [
            'title'  => 'FlightMeet - Detail',
            'active' => 'meetups',
            'meetup' => $meetup,
            'from_group' => $meetup['from_group'],
        ]));
    }

    /**
     * Für ein Treffen anmelden (Model prüft Kapazitätsgrenzen)
     */
    public function join(int $id): ResponseInterface
    {
        $userId = session()->get('fm_user_id');

        if ($this->meetupModel->joinMeetup($id, $userId)) {
            return redirect()->to('flightmeet/meetups/' . $id)->with('success', 'Du hast dich erfolgreich angemeldet!');
        }

        return redirect()->to('flightmeet/meetups/' . $id)->with('error', 'Anmeldung fehlgeschlagen (Treffen ist bereits voll).');
    }

    /**
     * Vom Treffen abmelden
     */
    public function leave(int $id): ResponseInterface
    {
        $userId = session()->get('fm_user_id');

        if ($this->meetupModel->leaveMeetup($id, $userId)) {
            return redirect()->to('flightmeet/meetups/' . $id)->with('success', 'Du hast dich erfolgreich abgemeldet.');
        }

        return redirect()->to('flightmeet/meetups/' . $id)->with('error', 'Abmeldung fehlgeschlagen.');
    }

    /**
     * Neues Flugtreffen erstellen
     */
    public function create(): ResponseInterface
    {
        if ($this->request->is('post')) {
            $creatorId = session()->get('fm_user_id');

            $title = $this->request->getPost('title');
            $location = $this->request->getPost('location');
            $region = $this->request->getPost('region');
            $meetDate = $this->request->getPost('meet_date');
            $meetTime = $this->request->getPost('meet_time');
            $level = $this->request->getPost('experience_level');
            $maxParticipants = $this->request->getPost('max_participants');
            $description = $this->request->getPost('description');
            $creatorIsPrivate = $this->request->getPost('creator_is_private') ? 1 : 0;
            $latitude = $this->request->getPost('latitude') ?: null;
            $longitude = $this->request->getPost('longitude') ?: null;

            // Pflichtfeld-Validierung
            if (empty($title) || empty($location) || empty($region) || empty($meetDate) || empty($meetTime) || empty($level) || empty($maxParticipants) || empty($latitude) || empty($description)) {
                return redirect()->back()->withInput()->with('error', 'Bitte füllen Sie alle erforderlichen Felder aus.');
            }

            if ((int)$maxParticipants < 1) {
                return redirect()->back()->withInput()->with('error', 'Die maximale Teilnehmerzahl muss mindestens 1 betragen.');
            }

            $data = [
                'creator_id'         => $creatorId,
                'creator_is_private' => $creatorIsPrivate,
                'title'              => $title,
                'location'           => $location,
                'region'             => $region,
                'meet_date'          => $meetDate,
                'meet_time'          => $meetTime,
                'experience_level'   => $level,
                'max_participants'   => (int)$maxParticipants,
                'description'        => $description,
                'status'             => 'geplant',
                'latitude'           => $latitude,
                'longitude'          => $longitude,
            ];

            $newMeetupId = $this->meetupModel->createMeetup($data);

            if ($newMeetupId) {
                return redirect()->to('flightmeet/meetups/' . $newMeetupId)->with('success', 'Flugtreffen erfolgreich erstellt!');
            }

            return redirect()->back()->withInput()->with('error', 'Das Erstellen des Treffens ist fehlgeschlagen.');
        }

        return $this->response->setBody(view('FlightMeet/meetupCreate', [
            'title'  => 'FlightMeet - Neues Treffen',
            'active' => 'meetups',
        ]));
    }

    /**
     * Flugtreffen bearbeiten (Eigentümer-exklusiv)
     */
    public function edit(int $id): ResponseInterface
    {
        $currentUserId = session()->get('fm_user_id');
        $meetup = $this->meetupModel->getMeetupById($id, $currentUserId);

        if ($meetup === null) {
            return redirect()->to('/flightmeet/meetups')->with('error', 'Flugtreffen wurde nicht gefunden.');
        }

        // Sicherheitsprüfung: Nur der Ersteller darf editieren
        if ((int)$meetup['creator_id'] !== (int)$currentUserId) {
            return redirect()->to('/flightmeet/meetups/' . $id)->with('error', 'Du bist nicht berechtigt, dieses Treffen zu bearbeiten.');
        }

        if ($this->request->is('post')) {
            $title = $this->request->getPost('title');
            $location = $this->request->getPost('location');
            $region = $this->request->getPost('region');
            $meetDate = $this->request->getPost('meet_date');
            $meetTime = $this->request->getPost('meet_time');
            $level = $this->request->getPost('experience_level');
            $maxParticipants = $this->request->getPost('max_participants');
            $description = $this->request->getPost('description');
            $creatorIsPrivate = $this->request->getPost('creator_is_private') ? 1 : 0;
            $latitude = $this->request->getPost('latitude') ?: null;
            $longitude = $this->request->getPost('longitude') ?: null;
            $status = $this->request->getPost('status') ?: 'geplant';

            // Validierung analog zur Erstellung
            if (empty($title) || empty($location) || empty($region) || empty($meetDate) || empty($meetTime) || empty($level) || empty($maxParticipants) || empty($latitude) || empty($description)) {
                return redirect()->back()->withInput()->with('error', 'Bitte füllen Sie alle erforderlichen Felder aus.');
            }

            if ((int)$maxParticipants < 1) {
                return redirect()->back()->withInput()->with('error', 'Die maximale Teilnehmerzahl muss mindestens 1 betragen.');
            }

            $data = [
                'creator_is_private' => $creatorIsPrivate,
                'title'              => $title,
                'location'           => $location,
                'region'             => $region,
                'meet_date'          => $meetDate,
                'meet_time'          => $meetTime,
                'experience_level'   => $level,
                'max_participants'   => (int)$maxParticipants,
                'description'        => $description,
                'status'             => $status,
                'latitude'           => $latitude,
                'longitude'          => $longitude,
            ];

            if ($this->meetupModel->update($id, $data)) {
                return redirect()->to('flightmeet/meetups/' . $id)->with('success', 'Flugtreffen erfolgreich aktualisiert!');
            }

            return redirect()->back()->withInput()->with('error', 'Das Aktualisieren des Treffens ist fehlgeschlagen.');
        }

        return $this->response->setBody(view('FlightMeet/meetupEdit', [
            'title'  => 'FlightMeet - Treffen bearbeiten',
            'active' => 'meetups',
            'meetup' => $meetup,
        ]));
    }

    /**
     * Flugtreffen löschen (Eigentümer-exklusiv)
     */
    public function delete(int $id): ResponseInterface
    {
        $currentUserId = session()->get('fm_user_id');
        $meetup = $this->meetupModel->find($id);

        if ($meetup === null) {
            return redirect()->to('/flightmeet/meetups')->with('error', 'Flugtreffen wurde nicht gefunden.');
        }

        // Sicherheitsprüfung: Nur der Ersteller darf das Inserat löschen
        if ((int)$meetup['creator_id'] !== (int)$currentUserId) {
            return redirect()->to('/flightmeet/meetups/' . $id)->with('error', 'Du bist nicht berechtigt, dieses Treffen zu löschen.');
        }

        if ($this->meetupModel->delete($id)) {
            return redirect()->to('flightmeet/meetups')->with('success', 'Flugtreffen erfolgreich gelöscht.');
        }

        return redirect()->to('flightmeet/meetups/' . $id)->with('error', 'Das Löschen des Treffens ist fehlgeschlagen.');
    }
}