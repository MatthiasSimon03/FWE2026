<?php

namespace App\Controllers;

use App\Models\SubmissionModel;
use App\Models\RallyModel;

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

        return view('leaderboard/index', [
            'rallies' => $rallies,
            'selectedRally' => $selectedRally,
            'leaderboard' => $leaderboard,
            'rallyId' => $rallyId
        ]);
    }
}

