<?php

namespace App\Models\FlightMeet;

use CodeIgniter\Model;

class MeetupModel extends Model
{
    protected $table = 'fm_flight_meets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'creator_id',
        'title',
        'location',
        'region',
        'meet_date',
        'meet_time',
        'experience_level',
        'max_participants',
        'description',
        'status',
    ];

    public function getMeetups(array $filters): array
    {
        $search = isset($filters['q']) ? trim((string) $filters['q']) : '';
        $region = isset($filters['region']) ? trim((string) $filters['region']) : '';
        $level = isset($filters['level']) ? trim((string) $filters['level']) : '';

        $builder = $this->db->table($this->table . ' fm');
        $builder
            ->select(
                'fm.id, fm.title, fm.location, fm.region, fm.description, fm.meet_date, fm.meet_time, '
                . 'fm.experience_level, fm.max_participants, fm.status, '
                . 'COUNT(p.user_id) AS participants_count'
            )
            ->join('fm_flight_meet_participants p', 'p.flight_meet_id = fm.id', 'left')
            ->groupBy('fm.id');

        if ($region !== '') {
            $builder->where('fm.region', $region);
        }

        if ($level !== '') {
            $builder->where('fm.experience_level', $level);
        }

        if ($search !== '') {
            $builder
                ->groupStart()
                ->like('fm.title', $search)
                ->orLike('fm.location', $search)
                ->orLike('fm.region', $search)
                ->orLike('fm.description', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function getFilterOptions(): array
    {
        $regions = $this->db->table($this->table)
            ->select('DISTINCT region', false)
            ->orderBy('region')
            ->get()
            ->getResultArray();

        $levels = $this->db->table($this->table)
            ->select('DISTINCT experience_level', false)
            ->orderBy('experience_level')
            ->get()
            ->getResultArray();

        $regions = array_map(static fn (array $row): string => $row['region'], $regions);
        $levels = array_map(static fn (array $row): string => $row['experience_level'], $levels);

        return [
            'regions' => $regions,
            'levels' => $levels,
        ];
    }
}

