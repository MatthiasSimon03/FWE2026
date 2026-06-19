<?php

namespace App\Controllers\FlightMeet;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FlightMeet\MeetupModel;

class Home extends BaseController
{
    public function index(): ResponseInterface
    {
        $meetupModel = new MeetupModel();


        // Statistiken für das Diagramm laden
        $regionStats = $meetupModel->getActiveMeetupsCountByRegion();

        return $this->response->setBody(view('FlightMeet/home', [
            'title'       => 'FlightMeet - Startseite',
            'active'      => 'home',
            'regionStats' => $regionStats, // Daten an die View übergeben
        ]));
    }
}