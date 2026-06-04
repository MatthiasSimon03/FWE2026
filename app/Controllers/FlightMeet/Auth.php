<?php

namespace App\Controllers\FlightMeet;
use App\Models\FlightMeet\UserModel;

class Auth extends BaseController
{
    protected $userModel;
    public function __construct(){
        $this->userModel = new UserModel();
    }

    public function register(){
        if ($this->request->is('post')) {
            $username = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $passwordConfirm = $this->request->getPost('password_confirm');
            $level = $this->request->getPost('experience_level');

            if (empty($username) || empty($email) || empty($password) || empty($passwordConfirm) || empty($level)) {
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

            if ($this->userModel->createUser($username, $email, $password, $level)) {
                return redirect()->to('flightmeet/auth/login')->with('success', 'Registrierung erfolgreich! Bitte anmelden.');
            }

            return redirect()->back()->withInput()->with('error', 'Registrierung fehlgeschlagen.');
        }

        return view('FlightMeet/auth/register');
    }

    public function login(){
        if ($this->request->is('post')) {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            $user = $this->userModel->verifyPassword($email, $password);
            if ($user) {
                session()->regenerate();
                session()->set([
                    'fm_user_id' => $user['id'],
                    'fm_username' => $user['username'],
                    'fm_email' => $user['email'],
                    'fm_role' => $user['role'],
                    'fm_experience_level' => $user['experience_level'],
                ]);
                return redirect()->to('flightmeet')->with('success', 'Anmeldung erfolgreich!');
            }
            return redirect()->back()->withInput()->with('error', 'E-Mail oder Passwort ist falsch.');
        }

        return view('FlightMeet/auth/login');
    }

    public function logout()
    {
        session()->remove(['fm_user_id', 'fm_username', 'fm_email', 'fm_role']);
        return redirect()->to('flightmeet/auth/login')->with('success', 'Abmeldung erfolgreich.');
    }

}