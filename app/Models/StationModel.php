<?php

namespace App\Models;

use CodeIgniter\Model;

class StationModel extends Model
{
    protected $table = 'stations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['rally_id', 'title', 'description', 'latitude', 'longitude', 'order_index'];
    protected $useTimestamps    = false;

    public function getStationsByRally($rallyId)
    {
        return $this->where('rally_id', $rallyId)->orderBy('order_index')->findAll();
    }

    public function getStationWithTasks($stationId)
    {
        $station = $this->find($stationId);
        if ($station) {
            $taskModel = new TaskModel();
            $station['tasks'] = $taskModel->where('station_id', $stationId)->findAll();
        }
        return $station;
    }
}

