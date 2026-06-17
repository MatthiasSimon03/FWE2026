<?php

namespace App\Controllers\FlightMeet;

use CodeIgniter\Controller;
use App\Models\FlightMeet\ChatModel;

class ChatController extends Controller
{
    protected ChatModel $chatModel;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
    }

    public function index()
    {
        $currentUserId = session()->get('fm_user_id');
        $db = \Config\Database::connect();

        // 1. Alle anderen Piloten laden (für Direktnachrichten)
        $pilots = $db->table('fm_users')
            ->select('id, username, experience_level')
            ->where('id !=', $currentUserId)
            ->orderBy('username', 'ASC')
            ->get()->getResultArray();

        // 2. Gruppen laden, in denen der Nutzer Mitglied ist
        $groups = $db->table('fm_groups g')
            ->select('g.id, g.name')
            ->join('fm_group_members m', 'm.group_id = g.id')
            ->where('m.user_id', $currentUserId)
            ->orderBy('g.name', 'ASC')
            ->get()->getResultArray();

        // 3. Meetups laden, bei denen der Nutzer angemeldet ist
        $meetups = $db->table('fm_flight_meets fm')
            ->select('fm.id, fm.title')
            ->join('fm_flight_meet_participants p', 'p.flight_meet_id = fm.id')
            ->where('p.user_id', $currentUserId)
            ->orderBy('fm.title', 'ASC')
            ->get()->getResultArray();

        $targetUserId = $this->request->getGet('user');

        return $this->response->setBody(view('FlightMeet/chat', [
            'title'          => 'FlightMeet - Chat',
            'active'         => 'chat',
            'pilots'         => $pilots,
            'groups'         => $groups,
            'meetups'        => $meetups,
            'target_user_id' => $targetUserId ? (int)$targetUserId : null,
        ]));
    }

    /**
     * AJAX Endpoint: Nachrichten abrufen
     */
    public function getMessages()
    {
        $currentUserId = session()->get('fm_user_id');
        $type = (string)$this->request->getGet('type');
        $targetId = $this->request->getGet('target_id') !== null && $this->request->getGet('target_id') !== '' ? (int)$this->request->getGet('target_id') : null;
        $offset = (int)($this->request->getGet('offset') ?? 0);

        // Sicherheitsprüfungen
        if ($type === 'group' && !$this->chatModel->isGroupMember($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Kein Gruppenmitglied.']);
        }
        if ($type === 'meetup' && !$this->chatModel->isMeetupParticipant($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Keine Anmeldung zu diesem Treffen gefunden.']);
        }

        $messages = $this->chatModel->getMessages($type, $targetId, $currentUserId, 50, $offset);

        return $this->response->setJSON([
            'success'  => true,
            'messages' => $messages,
            'userId'   => $currentUserId
        ]);
    }

    /**
     * AJAX Endpoint: Nachricht absenden
     */
    public function sendMessage()
    {
        $currentUserId = session()->get('fm_user_id');
        $type = (string)$this->request->getPost('type');
        $targetId = $this->request->getPost('target_id') !== null && $this->request->getPost('target_id') !== '' ? (int)$this->request->getPost('target_id') : null;
        $messageText = trim((string)$this->request->getPost('message_text'));

        if (empty($messageText)) {
            return $this->response->setJSON(['error' => 'Nachricht darf nicht leer sein.']);
        }

        if ($type === 'group' && !$this->chatModel->isGroupMember($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Nicht autorisiert.']);
        }
        if ($type === 'meetup' && !$this->chatModel->isMeetupParticipant($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Nicht autorisiert.']);
        }

        $data = [
            'sender_id'    => $currentUserId,
            'message_text' => $messageText,
            'created_at'   => date('Y-m-d H:i:s')
        ];

        if ($type === 'global') {
            // bleibt null
        } elseif ($type === 'dm') {
            $data['recipient_id'] = $targetId;
        } elseif ($type === 'group') {
            $data['group_id'] = $targetId;
        } elseif ($type === 'meetup') {
            $data['flight_meet_id'] = $targetId;
        }

        if ($this->chatModel->insert($data)) {
            return $this->response->setJSON(['success' => true]);
        }

        return $this->response->setJSON(['error' => 'Fehler beim Speichern der Nachricht.']);
    }
}