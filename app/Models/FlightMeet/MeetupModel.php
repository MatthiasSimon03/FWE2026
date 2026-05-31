<?php

namespace App\Models\FlightMeet;

class MeetupModel
{
    public function getAllMeetups(): array
    {
        return [
            [
                'title' => 'Sonnenaufgang am See',
                'spot' => 'Seeblick-Parkplatz',
                'region' => 'Bayern',
                'date' => '2026-06-07',
                'time' => '05:30',
                'level' => 'Einsteiger',
                'participants' => 8,
                'max_participants' => 15,
                'status' => 'geplant',
            ],
            [
                'title' => 'Thermik-Session am Berg',
                'spot' => 'Hochgrat Südhang',
                'region' => 'Allgäu',
                'date' => '2026-06-14',
                'time' => '11:00',
                'level' => 'Fortgeschritten',
                'participants' => 12,
                'max_participants' => 20,
                'status' => 'aktiv',
            ],
            [
                'title' => 'Abendflug über der City',
                'spot' => 'Skyline-Startpunkt',
                'region' => 'Nordrhein-Westfalen',
                'date' => '2026-06-21',
                'time' => '19:30',
                'level' => 'Alle Levels',
                'participants' => 18,
                'max_participants' => 25,
                'status' => 'geplant',
            ],
            [
                'title' => 'Sicherheits-Refresher',
                'spot' => 'Flugschule Nord',
                'region' => 'Niedersachsen',
                'date' => '2026-05-18',
                'time' => '09:00',
                'level' => 'Einsteiger',
                'participants' => 10,
                'max_participants' => 10,
                'status' => 'abgeschlossen',
            ],
        ];
    }
}

