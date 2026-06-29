<?php

namespace App\Models\FlightMeet;

use CodeIgniter\Model;

class GroupJoinRequestModel extends Model
{
    protected $table = 'fm_group_join_requests';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'group_id',
        'user_id',
        'status',
        'message',
        'requested_at',
        'handled_by',
        'handled_at'
    ];

    /**
     * Überprüft, ob bereits eine ausstehende (pending) Anfrage existiert
     */
    public function hasPendingRequest(int $groupId, int $userId): bool
    {
        return $this->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->countAllResults() > 0;
    }

    /**
     * Holt alle offenen Anfragen für eine Gruppe (für Admins/Owner)
     */
    public function getPendingRequests(int $groupId): array
    {
        return $this->db->table($this->table . ' r')
            ->select('r.*, u.username, u.experience_level')
            ->join('fm_users u', 'u.id = r.user_id')
            ->where('r.group_id', $groupId)
            ->where('r.status', 'pending')
            ->orderBy('r.requested_at', 'ASC')
            ->get()
            ->getResultArray();
    }
}