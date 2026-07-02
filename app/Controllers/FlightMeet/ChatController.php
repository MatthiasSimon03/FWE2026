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

    /**
     * Hauptansicht des Chats
     */
    public function index()
    {
        $currentUserId = session()->get('fm_user_id');

        // Sidebar-Inhalte laden (andere Piloten, Gruppen in denen User Mitglied ist, Treffen an denen er teilnimmt)
        $pilots     = $this->userModel->getOtherPilots($currentUserId);
        $groups     = $this->userModel->getUserGroups($currentUserId);
        $allMeetups = $this->meetupModel->getUserMeetups($currentUserId);

        $activeMeetups = [];
        $pastMeetups = [];

        // Treffen für Sidebar-Struktur aufteilen (geplant, ausgebucht / abgeschlossen, abgesagt)
        foreach ($allMeetups as $m) {
            if (in_array($m['status'], ['geplant', 'ausgebucht'], true)) {
                $activeMeetups[] = $m;
            } else {
                $pastMeetups[] = $m;
            }
        }

        $targetUserId = $this->request->getGet('user');

        return $this->response->setBody(view('FlightMeet/chat', [
            'title'          => 'FlightMeet - Chat',
            'active'         => 'chat',
            'pilots'         => $pilots,
            'groups'         => $groups,
            'active_meetups' => $activeMeetups,
            'past_meetups'   => $pastMeetups,
            'target_user_id' => $targetUserId ? (int)$targetUserId : null,
        ]));
    }

    /**
     * AJAX Endpoint: Nachrichten für einen Chat laden (mit Offset für Lazy Loading)
     */
    public function getMessages()
    {
        $currentUserId = session()->get('fm_user_id');
        $type          = (string)$this->request->getGet('type');   // group, meetup, global, dm
        $targetId      = $this->request->getGet('target_id') !== null && $this->request->getGet('target_id') !== '' ? (int)$this->request->getGet('target_id') : null;  // targetId ist die GruppenId, MeetId oder userId
        $offset        = (int)($this->request->getGet('offset') ?? 0);

        // Zugriffsschutz: Darf der User in dieser Gruppe / diesem Treffen mitlesen?
        if ($type === 'group' && !$this->chatModel->isGroupMember($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Kein Gruppenmitglied.']);
        }
        if ($type === 'meetup' && !$this->chatModel->isMeetupParticipant($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Keine Anmeldung zu diesem Treffen gefunden.']);
        }

        // Standardmäßig 50 Nachrichten laden, ältere werden bei Bedarf per Scroll nachgeladen
        $messages = $this->chatModel->getMessages($type, $targetId, $currentUserId, 50, $offset);

        return $this->response->setJSON([
            'success'  => true,
            'messages' => $messages,
            'userId'   => $currentUserId
        ]);
    }

    /**
     * AJAX Endpoint: Neue Nachricht speichern
     */
    public function sendMessage()
    {
        $currentUserId = session()->get('fm_user_id');
        $type          = (string)$this->request->getPost('type');
        $targetId      = $this->request->getPost('target_id') !== null && $this->request->getPost('target_id') !== '' ? (int)$this->request->getPost('target_id') : null;
        $messageText   = trim((string)$this->request->getPost('message_text'));

        // Spam-Schutz & Payload-Limitierung
        if (empty($messageText) || mb_strlen($messageText) > 2000) {
            return $this->response->setJSON(['error' => 'Nachricht darf nicht leer sein und maximal 2000 Zeichen enthalten.']);
        }

        // Berechtigungsprüfung vor dem Schreiben
        if ($type === 'group' && !$this->chatModel->isGroupMember($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Nicht autorisiert.']);
        }
        if ($type === 'meetup' && !$this->chatModel->isMeetupParticipant($targetId, $currentUserId)) {
            return $this->response->setJSON(['error' => 'Nicht autorisiert.']);
        }

        // Empfänger-Existenz bei Direktnachrichten prüfen
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

        // Foreign Keys je nach Chat-Typ zuweisen
        if ($type === 'global') {
            // Globaler Chat benötigt keinen foreign key
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