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
        $meetupModel = new MeetupModel();

        // 1. Wenn das Formular abgeschickt wurde (POST)
        if ($this->request->is('post')) {
            $creatorId = session()->get('fm_user_id'); // Ersteller-ID aus Session holen

            $title = $this->request->getPost('title');
            $location = $this->request->getPost('location');
            $region = $this->request->getPost('region');
            $meetDate = $this->request->getPost('meet_date');
            $meetTime = $this->request->getPost('meet_time');
            $level = $this->request->getPost('experience_level');
            $maxParticipants = $this->request->getPost('max_participants');
            $description = $this->request->getPost('description');
            $creatorIsPrivate = $this->request->getPost('creator_is_private') ? 1 : 0;

            $latitude = $this->request->getPost('latitude') ?: null;
            $longitude = $this->request->getPost('longitude') ?: null;

            // 2. Einfache Validierung (wie im Auth-Controller)
            if (empty($title) || empty($location) || empty($region) || empty($meetDate) || empty($meetTime) || empty($level) || empty($maxParticipants) || empty($latitude) || empty($description))  {
                return redirect()->back()->withInput()->with('error', 'Bitte füllen Sie alle erforderlichen Felder aus.');
            }

            if ((int)$maxParticipants < 1) {
                return redirect()->back()->withInput()->with('error', 'Die maximale Teilnehmerzahl muss mindestens 1 betragen.');
            }

            // 3. Daten für die Insert-Query vorbereiten
            $data = [
                'creator_id'         => $creatorId,
                'creator_is_private' => $creatorIsPrivate,
                'title'              => $title,
                'location'           => $location,
                'region'             => $region,
                'meet_date'          => $meetDate,
                'meet_time'          => $meetTime,
                'experience_level'   => $level,
                'max_participants'   => (int)$maxParticipants,
                'description'        => $description,
                'status'             => 'geplant',
                'latitude'           => $latitude,
                'longitude'          => $longitude,
            ];

            $newMeetupId = $meetupModel->createMeetup($data);

            if ($newMeetupId) {
                return redirect()->to('flightmeet/meetups/' . $newMeetupId)->with('success', 'Flugtreffen erfolgreich erstellt!');
            }

            return redirect()->back()->withInput()->with('error', 'Das Erstellen des Treffens ist fehlgeschlagen.');
        }


        // 2. Wenn die Seite normal aufgerufen wird (GET)
        return $this->response->setBody(view('FlightMeet/meetupCreate', [
            'title'  => 'FlightMeet - Neues Treffen',
            'active' => 'meetups',
        ]));

    }
}