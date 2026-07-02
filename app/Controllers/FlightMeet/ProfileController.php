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

        // POST-ANFRAGE: Profil aktualisieren
        if ($this->request->is('post')) {
            $newUsername = trim((string) $this->request->getPost('username'));
            $newLevel    = $this->request->getPost('experience_level');

            if (empty($newUsername) || empty($newLevel)) {
                return $this->response->setBody(redirect()->to('flightmeet/profile')->with('error', 'Benutzername und Erfahrungslevel dürfen nicht leer sein.'));
            }

            // Prüfen, ob der neue Benutzername bereits von einem anderen Piloten verwendet wird
            $existingUser = $userModel->where('username', $newUsername)->where('id !=', $userId)->first();
            if ($existingUser) {
                return $this->response->setBody(redirect()->to('flightmeet/profile')->with('error', 'Dieser Benutzername wird bereits verwendet.'));
            }

            // DB-Daten aktualisieren
            $userModel->update($userId, [
                'username'         => $newUsername,
                'experience_level' => $newLevel
            ]);

            // Session-Variablen für das Header-Menü anpassen
            session()->set([
                'fm_username'         => $newUsername,
                'fm_experience_level' => $newLevel
            ]);

            return redirect()->to('flightmeet/profile')->with('success', 'Dein Profil wurde erfolgreich aktualisiert.');
        }

        // GET-ANFRAGE: Profildaten, Gruppen und Flüge laden
        $user = $userModel->find($userId);
        $joinedGroups = $userModel->getUserGroups($userId);
        $scheduledFlights = $meetupModel->getFullUserMeetups($userId, ['geplant', 'ausgebucht']);
        $historicFlights = $meetupModel->getFullUserMeetups($userId, ['abgeschlossen', 'abgesagt']);

        // Statistiken aus den bestehenden Tabellen berechnen
        $db = \Config\Database::connect();

        // 1. Absolvierte Flüge (Anzahl abgeschlossener Treffen, an denen der User teilgenommen hat)
        $completedFlightsCount = $db->table('fm_flight_meet_participants p')
            ->join('fm_flight_meets fm', 'fm.id = p.flight_meet_id')
            ->where('p.user_id', $userId)
            ->where('fm.status', 'abgeschlossen')
            ->countAllResults();

        // 2. Organisierte Treffen (Anzahl vom User erstellter Treffen)
        $createdFlightsCount = $db->table('fm_flight_meets')
            ->where('creator_id', $userId)
            ->countAllResults();

        // 3. Flüge des aktuellen Kalenderjahres für das Saisondiagramm zählen
        $currentYear = date('Y');
        $monthsData = array_fill(1, 12, 0); // Jan (1) bis Dez (12) mit 0 vorbelegen

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
            'stats'             => [
                'completed' => $completedFlightsCount,
                'created'   => $createdFlightsCount,
            ],
            // Indizes für JavaScript auf 0 bis 11 normalisieren:
            'months_data'       => array_values($monthsData)
        ]));
    }
}