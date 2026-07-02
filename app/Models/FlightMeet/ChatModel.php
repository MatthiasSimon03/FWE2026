<?php

namespace App\Models\FlightMeet;

use CodeIgniter\Model;

class ChatModel extends Model
{
    // Hauptdatenbank-Tabelle für das integrierte Nachrichtensystem
    protected $table = 'fm_chat_messages';
    protected $primaryKey = 'id';
    protected $allowedFields = ['sender_id', 'message_text', 'group_id', 'flight_meet_id', 'recipient_id', 'created_at'];
    protected $returnType = 'array';

    /**
     * Holt den Chatverlauf für einen spezifischen Kontext mit Offset-Unterstützung (Lazy Loading).
     */
    public function getMessages(string $type, ?int $targetId, int $currentUserId, int $limit = 50, int $offset = 0): array
    {
        $builder = $this->db->table($this->table . ' m')
            ->select('m.id, m.sender_id, m.message_text, m.created_at, u.username as sender_name')
            // Nutzername des Absenders direkt mitladen
            ->join('fm_users u', 'u.id = m.sender_id')
            ->orderBy('m.created_at', 'DESC')
            ->limit($limit, $offset);

        if ($type === 'global') {
            // Globaler Chat: Keine Zuweisung zu Gruppen, Treffen oder dedizierten Empfängern
            $builder->where('m.group_id', null)
                ->where('m.flight_meet_id', null)
                ->where('m.recipient_id', null);
        } elseif ($type === 'dm' && $targetId !== null) {
            // Direktnachrichten: Wechselseitigen Nachrichtenverlauf zwischen beiden Usern abfragen
            $builder->groupStart()
                // aktueller User ist Sender
                ->where('m.sender_id', $currentUserId)->where('m.recipient_id', $targetId)
                ->groupEnd()
                ->orGroupStart()
                //aktueller Nutzer ist Empfänger
                ->where('m.sender_id', $targetId)->where('m.recipient_id', $currentUserId)
                ->groupEnd()
                ->where('m.group_id', null)
                ->where('m.flight_meet_id', null);
        } elseif ($type === 'group' && $targetId !== null) {
            $builder->where('m.group_id', $targetId);
        } elseif ($type === 'meetup' && $targetId !== null) {
            $builder->where('m.flight_meet_id', $targetId);
        } else {
            return [];
        }

        $results = $builder->get()->getResultArray();

        // Über 'DESC' werden die neuesten X Nachrichten aus der DB.
        // Für den Chatverlauf im UI müssen diese jedoch chronologisch (älteste oben) ausgegeben werden.
        return array_reverse($results);
    }

    /**
     * Sicherheitsprüfung: Ist der Nutzer berechtigt, in einer Gruppe zu lesen/schreiben?
     */
    public function isGroupMember(int $groupId, int $userId): bool
    {
        return $this->db->table('fm_group_members')
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->countAllResults() > 0;
    }

    /**
     * Sicherheitsprüfung: Ist der Nutzer berechtigt, im Meetup-Chat zu lesen/schreiben?
     */
    public function isMeetupParticipant(int $meetupId, int $userId): bool
    {
        return $this->db->table('fm_flight_meet_participants')
                ->where('flight_meet_id', $meetupId)
                ->where('user_id', $userId)
                ->countAllResults() > 0;
    }

    /**
     * Gibt alle anderen registrierten Piloten für die DM-Kontaktliste zurück
     */
    public function getOtherPilots(int $currentUserId): array
    {
        return $this->db->table('fm_users')
            ->select('id, username, experience_level')
            ->where('id !=', $currentUserId)
            ->orderBy('username', 'ASC')
            ->get()->getResultArray();
    }
}