<?php

namespace App\Controllers\Stadtrallye;

use App\Models\Stadtrallye\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function register()
    {
        if ($this->request->is('post')) {
            $name = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $passwordConfirm = $this->request->getPost('password_confirm');

            if (!$name || !$email || !$password) {
                return redirect()->back()->withInput()->with('error', 'Alle Felder sind erforderlich.');
            }

            if ($password !== $passwordConfirm) {
                return redirect()->back()->withInput()->with('error', 'Passwörter stimmen nicht überein.');
            }

            if (strlen($password) < 6) {
                return redirect()->back()->withInput()->with('error', 'Passwort muss mindestens 6 Zeichen lang sein.');
            }

            if ($this->userModel->getUserByEmail($email)) {
                return redirect()->back()->withInput()->with('error', 'E-Mail-Adresse wird bereits verwendet.');
            }

            if ($this->userModel->createUser($name, $email, $password)) {
                return redirect()->to(site_url('stadtrallye/auth/login'))->with('success', 'Registrierung erfolgreich! Bitte melden Sie sich an.');
            }

            return redirect()->back()->withInput()->with('error', 'Registrierung fehlgeschlagen. Versuchen Sie es später erneut.');
        }

        return view('Stadtrallye/auth/register');
    }

    public function login()
    {
        if ($this->request->is('post')) {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $user = $this->userModel->verifyPassword($email, $password);

            if ($user) {
                session()->regenerate();
                session()->set([
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]);
                return redirect()->to(site_url('stadtrallye/rally'))->with('success', 'Anmeldung erfolgreich!');
            }

            return redirect()->back()->withInput()->with('error', 'E-Mail oder Passwort ist falsch.');
        }

        return view('Stadtrallye/auth/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(site_url('stadtrallye/rally'))->with('success', 'Abmeldung erfolgreich.');
    }
}
