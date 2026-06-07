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
        $currentUserId = session()->get('fm_user_id');
        $meetup = $meetupModel->getMeetupById($id, $currentUserId);

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

    public function joinMeetup(int $id): ResponseInterface
    {
        $userId = session()->get('fm_user_id');
        $meetupModel = new MeetupModel();

        if ($meetupModel->joinMeetup($id, $userId)) {
            return redirect()->to('flightmeet/meetups/' . $id)->with('success', 'Du hast dich erfolgreich angemeldet!');
        }

        return redirect()->to('flightmeet/meetups/' . $id)->with('error', 'Anmeldung fehlgeschlagen (Treffen ist bereits voll).');
    }

    public function leaveMeetup(int $id): ResponseInterface
    {
        $userId = session()->get('fm_user_id');
        $meetupModel = new MeetupModel();

        if ($meetupModel->leaveMeetup($id, $userId)) {
            return redirect()->to('flightmeet/meetups/' . $id)->with('success', 'Du hast dich erfolgreich abgemeldet.');
        }

        return redirect()->to('flightmeet/meetups/' . $id)->with('error', 'Abmeldung fehlgeschlagen.');
    }

    public function createMeetup(): ResponseInterface {
        if ($this->request->is('post')) {
            return redirect()->to('flightmeet/meetups')->with('success', 'Flugtreffen wurde erfolgreich erstellt (funktioniert noch nicht wirklich).');
        }


        // 2. Wenn die Seite normal aufgerufen wird (GET)
        return $this->response->setBody(view('FlightMeet/meetupCreate', [
            'title'  => 'FlightMeet - Neues Treffen',
            'active' => 'meetups',
        ]));

    }
}