<?php

namespace App\Models\Stadtrallye;

class StationModel extends \App\Models\StationModel
{
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

