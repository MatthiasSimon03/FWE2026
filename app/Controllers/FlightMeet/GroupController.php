<?php

namespace App\Controllers\FlightMeet;

use CodeIgniter\HTTP\ResponseInterface;

class GroupController extends BaseController
{
    public function index(): ResponseInterface
    {
        return $this->response->setBody(view('FlightMeet/groups', [
            'title'  => 'FlightMeet - Gruppen',
            'active' => 'groups',
        ]));
    }
}