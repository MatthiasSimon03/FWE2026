<?php

namespace App\Controllers\FlightMeet;

use CodeIgniter\HTTP\ResponseInterface;

class ProfileController extends BaseController
{
    public function index(): ResponseInterface
    {
        return $this->response->setBody(view('FlightMeet/profile', [
            'title' => 'FlightMeet - Profil',
            'active' => 'profile',
        ]));
    }
}