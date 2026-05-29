<?php

namespace App\Controllers\Stadtrallye\Admin;

use App\Models\Stadtrallye\RallyModel;

class Rallies extends \App\Controllers\Stadtrallye\BaseController
{
    protected $rallyModel;

    public function __construct()
    {
        $this->rallyModel = new RallyModel();
    }

    public function checkAdmin()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Zugriff verweigert.');
        }
    }

    public function index()
    {
        $this->checkAdmin();
        $rallies = $this->rallyModel->findAll();
        return view('Stadtrallye/admin/rallies/list', ['rallies' => $rallies]);
    }

    public function create()
    {
        $this->checkAdmin();

        if ($this->request->is('post')) {
            $title = $this->request->getPost('title');
            $description = $this->request->getPost('description');
            $startTime = $this->request->getPost('start_time');
            $endTime = $this->request->getPost('end_time');

            if (!$title) {
                return redirect()->back()->with('error', 'Titel ist erforderlich.');
            }

            $this->rallyModel->insert([
                'title' => $title,
                'description' => $description,
                'start_time' => $startTime ?: null,
                'end_time' => $endTime ?: null,
                'is_active' => 1
            ]);

            return redirect()->to(site_url('stadtrallye/admin/rallies'))->with('success', 'Rallye erstellt.');
        }

        return view('Stadtrallye/admin/rallies/create');
    }

    public function edit($rallyId)
    {
        $this->checkAdmin();
        $rally = $this->rallyModel->find($rallyId);

        if (!$rally) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Rallye nicht gefunden.');
        }

        if ($this->request->is('post')) {
            $this->rallyModel->update($rallyId, [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'start_time' => $this->request->getPost('start_time') ?: null,
                'end_time' => $this->request->getPost('end_time') ?: null,
                'is_active' => $this->request->getPost('is_active') ? 1 : 0
            ]);

            return redirect()->to(site_url('stadtrallye/admin/rallies'))->with('success', 'Rallye aktualisiert.');
        }

        return view('Stadtrallye/admin/rallies/edit', ['rally' => $rally]);
    }

    public function delete($rallyId)
    {
        $this->checkAdmin();
        $this->rallyModel->delete($rallyId);
        return redirect()->to(site_url('stadtrallye/admin/rallies'))->with('success', 'Rallye gelöscht.');
    }
}
