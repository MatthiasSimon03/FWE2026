<?php

namespace App\Controllers\FlightMeet;

use CodeIgniter\Controller;

class ChatController extends Controller
{
    public function index()
    {
        return $this->response->setBody(view('FlightMeet/chat', [
            'title' => 'FlightMeet - Chat',
            'active' => 'chat',
        ]));
    }

}