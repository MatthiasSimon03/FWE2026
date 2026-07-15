<?php

namespace App\Controllers\FlightMeet;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FlightMeet\MeetupModel;
use App\Models\FlightMeet\GroupModel;

class Home extends BaseController
{
    public function index(): ResponseInterface
    {
        $meetupModel = new MeetupModel();
        $groupModel  = new GroupModel(); // NEU

        $currentUserId = (int) session()->get('fm_user_id');
        $username = session()->get('fm_username') ?? 'Pilot';

        // 1. Das nächste anstehende Treffen des Users ermitteln (für das Wetter-Widget)
        $upcomingMeetups = $meetupModel->getFullUserMeetups($currentUserId, ['geplant', 'ausgebucht']);
        $weatherData = null;
        $nextMeetupData = null;

        if (!empty($upcomingMeetups)) {
            $nextMeetup = $upcomingMeetups[0];

            $nextMeetupData = [
                'id'       => (int)$nextMeetup['id'],
                'title'    => $nextMeetup['title'],
                'location' => $nextMeetup['location'],
                'date'     => date('d.m.Y', strtotime($nextMeetup['meet_date'])),
                'time'     => date('H:i', strtotime($nextMeetup['meet_time'])),
            ];

            $lat = $nextMeetup['latitude'];
            $lon = $nextMeetup['longitude'];

            if ($lat !== null && $lon !== null) {
                $weatherData = $this->fetchOpenMeteoWeather((float)$lat, (float)$lon, $nextMeetup['meet_date'], $nextMeetup['meet_time']);
            }
        }

        // 2. Die 3 neuesten Flugtreffen aus den eigenen Gruppen laden
        $latestGroupMeetups = $meetupModel->getLatestGroupMeetups($currentUserId, 3);

        // 3. NEU: Personalisierte Gruppen-Empfehlungen laden (Top 2 passendsten Gruppen)
        $recommendedGroups = $groupModel->getRecommendedGroups($currentUserId, 2);

        // 4. Übergabe an die View
        return $this->response->setBody(view('FlightMeet/home', [
            'title'              => 'FlightMeet - Startseite',
            'active'             => 'home',
            'username'           => $username,
            'weather'            => $weatherData,
            'nextMeetup'         => $nextMeetupData,
            'latestGroupMeetups' => $latestGroupMeetups,
            'recommendedGroups'  => $recommendedGroups, // NEU
        ]));
    }

    /**
     * Hilfsmethode zur Abfrage der stündlichen Vorhersage für einen konkreten Zeitpunkt
     */
    private function fetchOpenMeteoWeather(float $lat, float $lon, string $date, string $time): ?array
    {
        // 1. Vorhersage-Zeitraum prüfen (Open-Meteo liefert max. 14 Tage im Voraus Daten)
        $today = date('Y-m-d');
        $daysDiff = (strtotime($date) - strtotime($today)) / 86400;

        // Wenn das Treffen in der Vergangenheit liegt oder mehr als 14 Tage in der Zukunft, keine API-Abfrage
        if ($daysDiff < 0 || $daysDiff > 14) {
            return null;
        }

        // 2. Ziel-Uhrzeit auf die volle Stunde runden (z.B. "10:30" -> "10:00")
        $targetHour = date('H', strtotime($time)) . ':00';
        $targetDateTime = $date . 'T' . $targetHour; // Format der API: "YYYY-MM-DDT00:00"

        // API URL für den konkreten Tag mit stündlichen (hourly) Daten
        $apiUrl = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&hourly=temperature_2m,weather_code,wind_speed_10m,wind_direction_10m&timezone=Europe/Berlin&start_date={$date}&end_date={$date}";

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                curl_close($ch);
                return null;
            }
            curl_close($ch);

            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            if (!is_array($data) || !isset($data['hourly']['time'])) {
                return null;
            }

            // 3. Den Index des stündlichen Arrays ermitteln, der mit unserer Ziel-Uhrzeit übereinstimmt
            $timesArray = $data['hourly']['time'];
            $index = array_search($targetDateTime, $timesArray);

            // Falls die genaue Stunde nicht gefunden wurde, brechen wir ab
            if ($index === false) {
                return null;
            }

            $hourly = $data['hourly'];

            // Werte für die spezifische Stunde auslesen
            $temp = $hourly['temperature_2m'][$index] ?? null;
            $wind = $hourly['wind_speed_10m'][$index] ?? null;
            $windDir = $hourly['wind_direction_10m'][$index] ?? null;
            $wmoCode = isset($hourly['weather_code'][$index]) ? (int)$hourly['weather_code'][$index] : null;

            if ($temp === null || $wind === null || $windDir === null || $wmoCode === null) {
                return null;
            }

            return [
                'temp'      => round($temp),
                'wind'      => round($wind),
                'wind_dir'  => $this->convertWindDirection((int)$windDir),
                'desc'      => $this->getWmoWeatherDescription($wmoCode),
                'icon'      => $this->getWmoWeatherIcon($wmoCode),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Übersetzt Gradzahlen in Himmelsrichtungen (wichtig für Gleitschirmflieger)
     */
    private function convertWindDirection(int $degree): string
    {
        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $index = round($degree / 22.5) % 16;
        return $directions[$index];
    }

    /**
     * WMO Weather Interpretation Codes (WMO) Übersetzung
     */
    private function getWmoWeatherDescription(int $code): string
    {
        $desc = [
            0 => 'Klarer Himmel',
            1 => 'Hauptsächlich klar', 2 => 'Teilweise bewölkt', 3 => 'Bedeckt',
            45 => 'Nebel', 48 => 'Reifnebel',
            51 => 'Leichter Nieselregen', 53 => 'Mäßiger Nieselregen', 55 => 'Dichter Nieselregen',
            61 => 'Leichter Regen', 63 => 'Mäßiger Regen', 65 => 'Starker Regen',
            71 => 'Leichter Schneefall', 73 => 'Mäßiger Schneefall', 75 => 'Starker Schneefall',
            80 => 'Leichte Regenschauer', 81 => 'Mäßige Regenschauer', 82 => 'Starke Regenschauer',
            95 => 'Gewitter', 96 => 'Gewitter mit Hagel', 99 => 'Gewitter mit starkem Hagel'
        ];

        return $desc[$code] ?? 'Unbekanntes Wetter';
    }

    /**
     * Passende Phosphor-Icons für Wettercodes
     */
    private function getWmoWeatherIcon(int $code): string
    {
        if ($code === 0) return 'ph-sun';
        if (in_array($code, [1, 2, 3])) return 'ph-cloud-sun';
        if (in_array($code, [45, 48])) return 'ph-cloud-fog';
        if (in_array($code, [51, 53, 55, 61, 63, 65, 80, 81, 82])) return 'ph-cloud-rain';
        if (in_array($code, [71, 73, 75])) return 'ph-snowflake';
        if (in_array($code, [95, 96, 99])) return 'ph-lightning';
        return 'ph-cloud';
    }
}