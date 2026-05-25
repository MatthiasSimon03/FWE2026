<?php

namespace App\Controllers\Admin;

use App\Models\TaskModel;
use App\Models\StationModel;

class Tasks extends \App\Controllers\BaseController
{
    protected $taskModel;
    protected $stationModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->stationModel = new StationModel();
    }

    public function checkAdmin()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Zugriff verweigert.');
        }
    }

    public function create($stationId)
    {
        $this->checkAdmin();

        // Lade Station, um die zugehörige Rallye-ID zu bekommen (für korrekte Back-Links)
        $station = $this->stationModel->find($stationId);
        if (!$station) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Station nicht gefunden.');
        }

        $rallyId = $station['rally_id'];

        if ($this->request->is('post')) {
            $this->taskModel->insert([
                'station_id' => $stationId,
                'text' => $this->request->getPost('text'),
                'answer' => $this->request->getPost('answer'),
                'points' => $this->request->getPost('points') ?: 1,
                'answer_type' => $this->request->getPost('answer_type') ?: 'text',
                'meta' => $this->request->getPost('meta') ?: null
            ]);

            return redirect()->to(site_url('admin/stations/' . $rallyId))->with('success', 'Aufgabe erstellt.');
        }

        return view('admin/tasks/create', ['stationId' => $stationId, 'rallyId' => $rallyId]);
    }

    public function edit($taskId)
    {
        $this->checkAdmin();
        $task = $this->taskModel->find($taskId);

        if (!$task) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Aufgabe nicht gefunden.');
        }

        if ($this->request->is('post')) {
            $this->taskModel->update($taskId, [
                'text' => $this->request->getPost('text'),
                'answer' => $this->request->getPost('answer'),
                'points' => $this->request->getPost('points') ?: 1,
                'answer_type' => $this->request->getPost('answer_type') ?: 'text',
                'meta' => $this->request->getPost('meta') ?: null
            ]);

            return redirect()->back()->with('success', 'Aufgabe aktualisiert.');
        }

        return view('admin/tasks/edit', ['task' => $task]);
    }

    public function delete($taskId)
    {
        $this->checkAdmin();
        $this->taskModel->delete($taskId);
        return redirect()->back()->with('success', 'Aufgabe gelöscht.');
    }
}
