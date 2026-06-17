<?php

namespace App\Models\FlightMeet;

use CodeIgniter\Model;

class ChatModel extends Model
{
    protected $table = 'fm_chat_messages';
    protected $primaryKey = 'id';
    protected $allowedFields = ['sender_id', 'message_text', 'group_id', 'flight_meet_id', 'recipient_id', 'created_at'];
    protected $returnType = 'array';

    /**
     * Holt alle Nachrichten für einen bestimmten Kontext (Global, DM, Group, Meetup) mit Offset für "Mehr laden".
     */
    public function getMessages(string $type, ?int $targetId, int $currentUserId, int $limit = 50, int $offset = 0): array
    {
        $builder = $this->db->table($this->table . ' m')
            ->select('m.id, m.sender_id, m.message_text, m.created_at, u.username as sender_name')
            // Join zur Nutzertabelle, um den Absendernamen zu erhalten
            ->join('fm_users u', 'u.id = m.sender_id')
            ->orderBy('m.created_at', 'DESC')
            ->limit($limit, $offset);

        if ($type === 'global') {
            $builder->where('m.group_id', null)
                ->where('m.flight_meet_id', null)
                ->where('m.recipient_id', null);
        } elseif ($type === 'dm' && $targetId !== null) {
            // Zeigt nur Nachrichten zwischen dem eingeloggten Nutzer und dem Zielnutzer
            $builder->groupStart()
                ->where('m.sender_id', $currentUserId)->where('m.recipient_id', $targetId)
                ->groupEnd()
                ->orGroupStart()
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

        // Da wir nach DESC sortiert haben (um die neuesten X Einträge zu greifen),
        // drehen wir das Ergebnis um, damit die ältesten Nachrichten oben stehen.
        return array_reverse($results);
    }

    /**
     * Prüft, ob ein Nutzer berechtigt ist, in einer Gruppe zu schreiben/lesen.
     */
    public function isGroupMember(int $groupId, int $userId): bool
    {
        return $this->db->table('fm_group_members')
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->countAllResults() > 0;
    }

    /**
     * Prüft, ob ein Nutzer berechtigt ist, im Meetup-Chat zu lesen/schreiben.
     */
    public function isMeetupParticipant(int $meetupId, int $userId): bool
    {
        return $this->db->table('fm_flight_meet_participants')
                ->where('flight_meet_id', $meetupId)
                ->where('user_id', $userId)
                ->countAllResults() > 0;
    }
}