<?php

namespace App\Models\FlightMeet;

use CodeIgniter\Model;

class GroupMemberModel extends Model
{
    protected $table = 'fm_group_members';
    protected $primaryKey = 'group_id'; // Zusammengesetzter primary Key wird manuell behandelt
    protected $returnType = 'array';
    protected $allowedFields = ['group_id', 'user_id', 'role', 'joined_at'];

    /**
     * Prüft, ob ein Benutzer Mitglied einer Gruppe ist
     */
    public function isMember(int $groupId, int $userId): bool
    {
        return $this->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->countAllResults() > 0;
    }

    /**
     * Holt die Rolle eines Benutzers in einer Gruppe
     */
    public function getUserRole(int $groupId, int $userId): ?string
    {
        $member = $this->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->first();
        return $member ? $member['role'] : null;
    }

    /**
     * Ruft die Mitgliederliste einer Gruppe inklusive Benutzerprofilen ab
     */
    public function getMembersWithDetails(int $groupId): array
    {
        return $this->db->table($this->table . ' gm')
            ->select('gm.*, u.username, u.experience_level, u.email')
            ->join('fm_users u', 'u.id = gm.user_id')
            ->where('gm.group_id', $groupId)
            ->orderBy("FIELD(gm.role, 'owner', 'admin', 'member')", '', false)
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Fügt ein neues Mitglied hinzu
     */
    public function addMember(int $groupId, int $userId, string $role = 'member'): bool
    {
        if ($this->isMember($groupId, $userId)) {
            return false;
        }

        return $this->db->table($this->table)->insert([
            'group_id' => $groupId,
            'user_id'  => $userId,
            'role'     => $role
        ]);
    }

    /**
     * Entfernt ein Mitglied
     */
    public function removeMember(int $groupId, int $userId): bool
    {
        return $this->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->delete() ? true : false;
    }
}