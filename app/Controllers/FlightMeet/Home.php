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
            'status' => ($this->request->getGet('status') ?? []),
        ];
        if (!is_array($filters['status'])) {
            $filters['status'] = [];
        }
        $options = $meetupModel->getFilterOptions();

        return $this->response->setBody(view('FlightMeet/meetups', [
            'title' => 'FlightMeet - Flugtreffen',
            'active' => 'meetups',
            'meetups' => $meetupModel->getMeetups($filters),
            'filters' => $filters,
            'options' => $options,
        ]));
    }

    public function meetupDetail(int $id): ResponseInterface
    {
        $meetupModel = new MeetupModel();
        $meetup = $meetupModel->getMeetupById($id);

        if ($meetup === null) {
            return redirect()
                ->to('/flightmeet/meetups')
                ->with('error', 'Flugtreffen wurde nicht gefunden.');
        }


        return $this->response->setBody(view('FlightMeet/meetupDetail', [
            'title' => 'FlightMeet - Detail',
            'active' => 'meetups',
            'meetup' => $meetup,
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

    public function profile(): ResponseInterface
    {
        return $this->response->setBody(view('FlightMeet/profile', [
            'title' => 'FlightMeet - Profil',
            'active' => 'profile',
        ]));
    }
}