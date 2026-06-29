<?php

namespace App\Models\FlightMeet;

use CodeIgniter\Model;

class GroupModel extends Model
{
    protected $table = 'fm_groups';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'description',
        'rules',
        'visibility',
        'region',
        'base_location',
        'latitude',
        'longitude',
        'created_by'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Holt alle Gruppen inklusive der aktuellen Mitgliederanzahl
     */
    public function getGroups(string $search = ''): array
    {
        $builder = $this->db->table($this->table . ' g');
        $builder->select('g.*, COUNT(m.user_id) as members_count')
            ->join('fm_group_members m', 'm.group_id = g.id', 'left')
            ->groupBy('g.id')
            ->orderBy('g.name', 'ASC');

        // Wenn ein Suchbegriff angegeben ist, filtere die Gruppen nach Name, Region oder Beschreibung
        if ($search !== '') {
            $builder->groupStart()
                ->like('g.name', $search)
                ->orLike('g.description', $search)
                ->orLike('g.region', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Holt eine einzelne Gruppe mit zusätzlichem Owner-Namen
     */
    public function getGroupById(int $id): ?array
    {
        return $this->select('fm_groups.*, u.username as owner_name')
            ->join('fm_users u', 'u.id = fm_groups.created_by')
            ->where('fm_groups.id', $id)
            ->first();
    }

    /**
     * Ermittelt die Flüge, die einer Gruppe zugeordnet sind.
     * Regel: Ersteller des Flugs muss aktuell Mitglied der Gruppe sein.
     */
    public function getGroupMeetups(int $groupId, array $statuses = ['geplant', 'ausgebucht']): array
    {
        return $this->db->table('fm_flight_meets fm')
            ->select('fm.*, u.username as creator_name, COUNT(p.user_id) as participants_count')
            // JOIN 1: Prüfen, ob der Ersteller des Flugs Mitglied der Gruppe ist
            ->join('fm_group_members gm', 'gm.user_id = fm.creator_id AND gm.group_id = ' . $this->db->escape($groupId))
            // JOIN 2: Ersteller-Details für optionale Anonymisierung
            ->join('fm_users u', 'u.id = fm.creator_id')
            // JOIN 3: Teilnehmerzahlen für die Flüge ermitteln
            ->join('fm_flight_meet_participants p', 'p.flight_meet_id = fm.id', 'left')
            ->whereIn('fm.status', $statuses)
            ->groupBy('fm.id')
            ->orderBy('fm.meet_date', 'ASC')
            ->orderBy('fm.meet_time', 'ASC')
            ->get()
            ->getResultArray();
    }
}