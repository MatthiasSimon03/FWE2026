<?php

namespace App\Controllers\Stadtrallye\Admin;

use App\Models\Stadtrallye\StationModel;
use App\Models\Stadtrallye\RallyModel;

class Stations extends \App\Controllers\Stadtrallye\BaseController
{
    protected $stationModel;
    protected $rallyModel;

    public function __construct()
    {
        $this->stationModel = new StationModel();
        $this->rallyModel = new RallyModel();
    }

    public function checkAdmin()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Zugriff verweigert.');
        }
    }

    public function index($rallyId = null)
    {
        $this->checkAdmin();

        if (!$rallyId) {
            $rallies = $this->rallyModel->findAll();
            if (empty($rallies)) {
                return redirect()->to(site_url('stadtrallye/admin/rallies'))->with('error', 'Es gibt keine Rallyen.');
            }
            return redirect()->to(site_url('stadtrallye/admin/stations/' . $rallies[0]['id']));
        }

        $rally = $this->rallyModel->find($rallyId);
        if (!$rally) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Rallye nicht gefunden.');
        }

        $stations = $this->stationModel->where('rally_id', $rallyId)->orderBy('order_index')->findAll();
        return view('Stadtrallye/admin/stations/list', ['rally' => $rally, 'stations' => $stations, 'rallyId' => $rallyId]);
    }

    public function create($rallyId)
    {
        $this->checkAdmin();

        if ($this->request->is('post')) {
            $this->stationModel->insert([
                'rally_id' => $rallyId,
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'latitude' => $this->request->getPost('latitude') ?: null,
                'longitude' => $this->request->getPost('longitude') ?: null,
                'order_index' => $this->request->getPost('order_index') ?: 0
            ]);

            return redirect()->to(site_url('stadtrallye/admin/stations/' . $rallyId))->with('success', 'Station erstellt.');
        }

        return view('Stadtrallye/admin/stations/create', ['rallyId' => $rallyId]);
    }

    public function edit($stationId)
    {
        $this->checkAdmin();
        $station = $this->stationModel->find($stationId);

        if (!$station) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Station nicht gefunden.');
        }

        if ($this->request->is('post')) {
            $this->stationModel->update($stationId, [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'latitude' => $this->request->getPost('latitude') ?: null,
                'longitude' => $this->request->getPost('longitude') ?: null,
                'order_index' => $this->request->getPost('order_index') ?: 0
            ]);

            return redirect()->to(site_url('stadtrallye/admin/stations/' . $station['rally_id']))->with('success', 'Station aktualisiert.');
        }

        return view('Stadtrallye/admin/stations/edit', ['station' => $station]);
    }

    public function delete($stationId)
    {
        $this->checkAdmin();
        $station = $this->stationModel->find($stationId);
        $rallyId = $station['rally_id'];
        $this->stationModel->delete($stationId);
        return redirect()->to(site_url('stadtrallye/admin/stations/' . $rallyId))->with('success', 'Station gelöscht.');
    }
}
