<?php

namespace App\Models;

use CodeIgniter\Model;

class RallyModel extends Model
{
    protected $table = 'rallies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['title', 'description', 'start_time', 'end_time', 'is_active', 'created_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField     = '';

    public function getActiveRallies()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function getRallyWithStations($rallyId)
    {
        $rally = $this->find($rallyId);
        if ($rally) {
            $stationModel = new StationModel();
            $rally['stations'] = $stationModel->where('rally_id', $rallyId)->orderBy('order_index')->findAll();
        }
        return $rally;
    }
}

