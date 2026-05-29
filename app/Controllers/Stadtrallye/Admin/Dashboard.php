<?php

namespace App\Controllers\Stadtrallye\Admin;

class Dashboard extends \App\Controllers\Stadtrallye\BaseController
{
    public function index()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to(site_url('stadtrallye/auth/login'))->with('error', 'Zugriff verweigert.');
        }

        return view('Stadtrallye/admin/dashboard');
    }
}
