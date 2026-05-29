<?php

namespace App\Controllers\Stadtrallye\Admin;

use App\Models\Stadtrallye\TaskModel;
use App\Models\Stadtrallye\StationModel;

class Tasks extends \App\Controllers\Stadtrallye\BaseController
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

    public function index($stationId)
    {
        $this->checkAdmin();

        $station = $this->stationModel->find($stationId);
        if (!$station) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Station nicht gefunden.');
        }

        $tasks = $this->taskModel->where('station_id', $stationId)->findAll();

        return view('Stadtrallye/admin/tasks/list', [
            'station' => $station,
            'tasks' => $tasks,
            'stationId' => $stationId,
            'rallyId' => $station['rally_id']
        ]);
    }

    public function create($stationId)
    {
        $this->checkAdmin();

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

            return redirect()->to(site_url('stadtrallye/admin/tasks/' . $stationId))->with('success', 'Aufgabe erstellt.');
        }

        return view('Stadtrallye/admin/tasks/create', ['stationId' => $stationId, 'rallyId' => $rallyId]);
    }

    public function edit($taskId)
    {
        $this->checkAdmin();
        $task = $this->taskModel->find($taskId);

        if (!$task) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Aufgabe nicht gefunden.');
        }

        $station = $this->stationModel->find($task['station_id']);
        if (!$station) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Station nicht gefunden.');
        }

        if ($this->request->is('post')) {
            $this->taskModel->update($taskId, [
                'text' => $this->request->getPost('text'),
                'answer' => $this->request->getPost('answer'),
                'points' => $this->request->getPost('points') ?: 1,
                'answer_type' => $this->request->getPost('answer_type') ?: 'text',
                'meta' => $this->request->getPost('meta') ?: null
            ]);

            return redirect()->to(site_url('stadtrallye/admin/tasks/' . $station['id']))->with('success', 'Aufgabe aktualisiert.');
        }

        return view('Stadtrallye/admin/tasks/edit', ['task' => $task, 'stationId' => $station['id'], 'rallyId' => $station['rally_id']]);
    }

    public function delete($taskId)
    {
        $this->checkAdmin();
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Aufgabe nicht gefunden.');
        }

        $station = $this->stationModel->find($task['station_id']);
        if (!$station) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Station nicht gefunden.');
        }

        $this->taskModel->delete($taskId);
        return redirect()->to(site_url('stadtrallye/admin/tasks/' . $station['id']))->with('success', 'Aufgabe gelöscht.');
    }
}
