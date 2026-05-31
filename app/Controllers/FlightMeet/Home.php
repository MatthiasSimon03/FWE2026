<?php

namespace App\Controllers\FlightMeet;

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
        return $this->response->setBody(view('FlightMeet/meetups', [
            'title' => 'FlightMeet - Flugtreffen',
            'active' => 'meetups',
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
}