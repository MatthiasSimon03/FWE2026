<?php

namespace App\Controllers\Stadtrallye;

use App\Models\Stadtrallye\SubmissionModel;
use App\Models\Stadtrallye\RallyModel;

class LeaderboardController extends BaseController
{
    protected $submissionModel;
    protected $rallyModel;

    public function __construct()
    {
        $this->submissionModel = new SubmissionModel();
        $this->rallyModel = new RallyModel();
    }

    public function index($rallyId = null)
    {
        $rallies = $this->rallyModel->getActiveRallies();

        if (!$rallyId && !empty($rallies)) {
            $rallyId = $rallies[0]['id'];
        }

        $leaderboard = [];
        $selectedRally = null;

        if ($rallyId) {
            $selectedRally = $this->rallyModel->find($rallyId);
            $leaderboard = $this->submissionModel->getLeaderboardForRally($rallyId);
        }

        return view('Stadtrallye/leaderboard/index', [
            'rallies' => $rallies,
            'selectedRally' => $selectedRally,
            'leaderboard' => $leaderboard,
            'rallyId' => $rallyId
        ]);
    }
}
