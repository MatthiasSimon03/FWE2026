<?php

namespace App\Controllers;

use App\Models\StationModel;
use App\Models\TaskModel;
use App\Models\SubmissionModel;

class StationController extends BaseController
{
    protected $stationModel;
    protected $taskModel;
    protected $submissionModel;

    public function __construct()
    {
        $this->stationModel = new StationModel();
        $this->taskModel = new TaskModel();
        $this->submissionModel = new SubmissionModel();
    }

    public function show($stationId)
    {
        if (!session()->get('user_id')) {
            return redirect()->to(site_url('auth/login'))->with('error', 'Bitte melden Sie sich zuerst an.');
        }

        $station = $this->stationModel->getStationWithTasks($stationId);
        if (!$station) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Station nicht gefunden.');
        }

        return view('station/show', ['station' => $station]);
    }

    public function submitAnswer($taskId)
    {
        if (!session()->get('user_id')) {
            return redirect()->to(site_url('auth/login'))->with('error', 'Bitte melden Sie sich zuerst an.');
        }

        if ($this->request->getMethod() !== 'POST') {
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        $submittedAnswer = $this->request->getPost('answer');

        if (!$submittedAnswer) {
            return redirect()->back()->with('error', 'Bitte geben Sie eine Antwort ein.');
        }

        if ($this->submissionModel->submitAnswer($userId, $taskId, $submittedAnswer)) {
            $taskModel = new TaskModel();
            $evaluation = $taskModel->evaluateAnswer($taskId, $submittedAnswer);

            $message = $evaluation['correct']
                ? "Richtig! Sie erhalten {$evaluation['points']} Punkte."
                : 'Leider falsch. Versuchen Sie es erneut.';

            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('error', 'Fehler beim Abspeichern der Antwort.');
    }
}
