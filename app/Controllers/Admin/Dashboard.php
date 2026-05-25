<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;

class Dashboard extends \App\Controllers\BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'admin') {
            return redirect()->to(site_url('auth/login'))->with('error', 'Zugriff verweigert.');
        }

        return view('admin/dashboard');
    }
}
