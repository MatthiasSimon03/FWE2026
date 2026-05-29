<?php

namespace App\Controllers\Stadtrallye;

use App\Models\Stadtrallye\RallyModel;
use App\Models\Stadtrallye\StationModel;
use App\Models\Stadtrallye\SubmissionModel;

class RallyController extends BaseController
{
    protected $rallyModel;
    protected $stationModel;
    protected $submissionModel;

    public function __construct()
    {
        $this->rallyModel = new RallyModel();
        $this->stationModel = new StationModel();
        $this->submissionModel = new SubmissionModel();
    }

    public function index()
    {
        $rallies = $this->rallyModel->getActiveRallies();
        return view('Stadtrallye/rally/list', ['rallies' => $rallies]);
    }

    public function show($rallyId)
    {
        $rally = $this->rallyModel->getRallyWithStations($rallyId);
        if (!$rally) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Rallye nicht gefunden.');
        }

        return view('Stadtrallye/rally/show', ['rally' => $rally]);
    }
}
