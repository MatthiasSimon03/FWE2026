<?php

namespace App\Controllers\Stadtrallye;

use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    public function index(): ResponseInterface
    {
        return redirect()->to(site_url('stadtrallye/rally'));
    }
}

