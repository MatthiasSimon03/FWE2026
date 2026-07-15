<?php

namespace App\Controllers\FlightMeet;

use App\Models\FlightMeet\UserModel;
use App\Models\FlightMeet\MeetupModel;
use App\Models\FlightMeet\UserRegionModel; // NEU
use CodeIgniter\HTTP\ResponseInterface;

class ProfileController extends BaseController
{
    public function index(): ResponseInterface
    {
        $userId = session()->get('fm_user_id');

        $userModel = new UserModel();
        $meetupModel = new MeetupModel();
        $userRegionModel = new UserRegionModel(); // NEU

        // POST-ANFRAGE: Profil aktualisieren
        if ($this->request->is('post')) {
            $newUsername = trim((string) $this->request->getPost('username'));
            $newLevel    = $this->request->getPost('experience_level');
            $regions     = $this->request->getPost('regions') ?? []; // NEU: Ausgewählte Regionen

            if (empty($newUsername) || empty($newLevel)) {
                return $this->response->setBody(redirect()->to('flightmeet/profile')->with('error', 'Benutzername und Erfahrungslevel dürfen nicht leer sein.'));
            }

            // Eindeutigkeit des Benutzernamens prüfen
            $existingUser = $userModel->where('username', $newUsername)->where('id !=', $userId)->first();
            if ($existingUser) {
                return $this->response->setBody(redirect()->to('flightmeet/profile')->with('error', 'Dieser Benutzername wird bereits verwendet.'));
            }

            // Profil-Daten aktualisieren
            $userModel->update($userId, [
                'username'         => $newUsername,
                'experience_level' => $newLevel
            ]);

            // NEU: Lieblingsregionen in der n:m Tabelle speichern
            $userRegionModel->saveUserRegions($userId, $regions);

            // Session-Variablen aktualisieren
            session()->set([
                'fm_username'         => $newUsername,
                'fm_experience_level' => $newLevel
            ]);

            return redirect()->to('flightmeet/profile')->with('success', 'Dein Profil wurde erfolgreich aktualisiert.');
        }

        // GET-ANFRAGE: Profildaten laden
        $user = $userModel->find($userId);
        $joinedGroups = $userModel->getUserGroups($userId);
        $scheduledFlights = $meetupModel->getFullUserMeetups($userId, ['geplant', 'ausgebucht']);
        $historicFlights = $meetupModel->getFullUserMeetups($userId, ['abgeschlossen', 'abgesagt']);

        // NEU: Regionen laden
        $allOptions = $meetupModel->getFilterOptions();
        $allRegions = $allOptions['regions'] ?? []; // Alle im System genutzten Regionen
        $userRegions = $userRegionModel->getUserRegions($userId); // Regionen des aktuellen Users

        // Statistiken berechnen
        $db = \Config\Database::connect();
        $completedFlightsCount = $db->table('fm_flight_meet_participants p')
            ->join('fm_flight_meets fm', 'fm.id = p.flight_meet_id')
            ->where('p.user_id', $userId)
            ->where('fm.status', 'abgeschlossen')
            ->countAllResults();

        $createdFlightsCount = $db->table('fm_flight_meets')
            ->where('creator_id', $userId)
            ->countAllResults();

        $currentYear = date('Y');
        $monthsData = array_fill(1, 12, 0);

        $allFlights = array_merge($scheduledFlights, $historicFlights);
        foreach ($allFlights as $flight) {
            $flightYear = date('Y', strtotime($flight['meet_date']));
            if ($flightYear === $currentYear) {
                $month = (int)date('m', strtotime($flight['meet_date']));
                $monthsData[$month]++;
            }
        }

        return $this->response->setBody(view('FlightMeet/profile', [
            'title'             => 'FlightMeet - Profil',
            'active'            => 'profile',
            'user'              => $user,
            'joined_groups'     => $joinedGroups,
            'scheduled_flights' => $scheduledFlights,
            'historic_flights'  => $historicFlights,
            'all_regions'       => $allRegions,   // NEU
            'user_regions'      => $userRegions,  // NEU
            'stats'             => [
                'completed' => $completedFlightsCount,
                'created'   => $createdFlightsCount,
            ],
            'months_data'       => array_values($monthsData)
        ]));
    }
}