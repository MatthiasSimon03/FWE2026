<?php

namespace App\Controllers\FlightMeet;

use App\Models\FlightMeet\UserModel;
use App\Models\FlightMeet\MeetupModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProfileController extends BaseController
{
    public function index(): ResponseInterface
    {
        $userId = session()->get('fm_user_id');

        $userModel = new UserModel();
        $meetupModel = new MeetupModel();

        // Profildaten holen
        $user = $userModel->find($userId);

        // Gruppen laden, in denen der User Mitglied ist
        $joinedGroups = $userModel->getUserGroups($userId);

        // Geplante und historische Flüge des Benutzers laden (Erstellt ODER Teilgenommen)
        $scheduledFlights = $meetupModel->getFullUserMeetups($userId, ['geplant', 'ausgebucht']);
        $historicFlights = $meetupModel->getFullUserMeetups($userId, ['abgeschlossen', 'abgesagt']);

        return $this->response->setBody(view('FlightMeet/profile', [
            'title'             => 'FlightMeet - Profil',
            'active'            => 'profile',
            'user'              => $user,
            'joined_groups'     => $joinedGroups,
            'scheduled_flights' => $scheduledFlights,
            'historic_flights'  => $historicFlights
        ]));
    }
}