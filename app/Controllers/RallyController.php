<?php

namespace App\Controllers;

use App\Models\RallyModel;
use App\Models\StationModel;
use App\Models\SubmissionModel;

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
        return view('rally/list', ['rallies' => $rallies]);
    }

    public function show($rallyId)
    {
        $rally = $this->rallyModel->getRallyWithStations($rallyId);
        if (!$rally) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Rallye nicht gefunden.');
        }
        return view('rally/show', ['rally' => $rally]);
    }
}

