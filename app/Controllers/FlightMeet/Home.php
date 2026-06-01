<?php

namespace App\Controllers\FlightMeet;

use App\Models\FlightMeet\MeetupModel;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    public function index(): ResponseInterface
    {
        return $this->response->setBody(view('FlightMeet/home', [
            'title' => 'FlightMeet - Startseite',
            'active' => 'home',
        ]));
    }

    public function meetups(): ResponseInterface
    {
        $meetupModel = new MeetupModel();
        $filters = [
            'q' => (string) ($this->request->getGet('q') ?? ''),
            'region' => (string) ($this->request->getGet('region') ?? ''),
            'level' => (string) ($this->request->getGet('level') ?? ''),
        ];
        $options = $meetupModel->getFilterOptions();

        return $this->response->setBody(view('FlightMeet/meetups', [
            'title' => 'FlightMeet - Flugtreffen',
            'active' => 'meetups',
            'meetups' => $meetupModel->getMeetups($filters),
            'filters' => $filters,
            'options' => $options,
        ]));
    }

    public function groups(): ResponseInterface
    {
        return $this->response->setBody(view('FlightMeet/groups', [
            'title' => 'FlightMeet - Gruppen',
            'active' => 'groups',
        ]));
    }

    public function chat(): ResponseInterface
    {
        return $this->response->setBody(view('FlightMeet/chat', [
            'title' => 'FlightMeet - Chat',
            'active' => 'chat',
        ]));
    }
}