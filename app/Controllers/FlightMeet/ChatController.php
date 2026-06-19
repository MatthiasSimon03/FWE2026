<?php

namespace App\Controllers\FlightMeet;

use CodeIgniter\Controller;
use App\Models\FlightMeet\ChatModel;
use App\Models\FlightMeet\UserModel;
use App\Models\FlightMeet\MeetupModel;

class ChatController extends Controller
{
    protected ChatModel $chatModel;
    protected UserModel $userModel;
    protected MeetupModel $meetupModel;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        $this->userModel = new UserModel();
        $this->meetupModel = new MeetupModel();
    }

    public function index()
    {
        $currentUserId = session()->get('fm_user_id');

        // Komplette Auslagerung der Datenbanklogik in die entsprechenden Models
        $pilots  = $this->userModel->getOtherPilots($currentUserId);
        $groups  = $this->userModel->getUserGroups($currentUserId);
        $meetups = $this->meetupModel->getUserMeetups($currentUserId);

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

        // 1. Validierung der Nachricht (Inhalt & Zeichenbegrenzung gegen Denial of Service)
        if (empty($messageText) || mb_strlen($messageText) > 2000) {
            return $this->response->setJSON(['error' => 'Nachricht darf nicht leer sein und maximal 2000 Zeichen enthalten.']);
        }

        // 2. Autorisierungsprüfung für Gruppen und Treffen
        if ($type === 'group' && !$this->chatModel->isGroupMember($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Nicht autorisiert.']);
        }
        if ($type === 'meetup' && !$this->chatModel->isMeetupParticipant($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Nicht autorisiert.']);
        }

        // 3. Sicherheitsvalidierung: Existiert der Empfänger bei Direktnachrichten (DMs)?
        if ($type === 'dm' && $targetId !== null) {
            $recipientExists = $this->userModel->find($targetId) !== null;
            if (!$recipientExists) {
                return $this->response->setJSON(['error' => 'Der ausgewählte Empfänger existiert nicht.']);
            }
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
