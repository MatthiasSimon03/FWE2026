<?php

namespace App\Models\Stadtrallye;

class RallyModel extends \App\Models\RallyModel
{
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

