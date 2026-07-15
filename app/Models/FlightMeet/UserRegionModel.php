<?php

namespace App\Models\FlightMeet;

use CodeIgniter\Model;

class UserRegionModel extends Model
{
    protected $table = 'fm_user_regions';
    protected $allowedFields = ['user_id', 'region'];

    /**
     * Holt alle ausgewählten Regionen eines Users als einfaches String-Array
     */
    public function getUserRegions(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();
        return array_column($rows, 'region');
    }

    /**
     * Aktualisiert die Lieblingsregionen eines Users (löscht alte, fügt neue ein)
     */
    public function saveUserRegions(int $userId, array $regions): void
    {
        // 1. Alle alten Einträge löschen
        $this->where('user_id', $userId)->delete();

        // 2. Neue Einträge im Batch einfügen
        if (!empty($regions)) {
            $data = [];
            foreach ($regions as $region) {
                $data[] = [
                    'user_id' => $userId,
                    'region'  => trim($region)
                ];
            }
            $this->insertBatch($data);
        }
    }
}