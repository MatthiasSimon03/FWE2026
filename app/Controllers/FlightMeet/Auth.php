<?php

namespace App\Controllers\FlightMeet;
use App\Models\FlightMeet\UserModel;
use CodeIgniter\I18n\Time; // Zum Rechnen mit Daten

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
            $rememberMe = $this->request->getPost('remember_me');

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

                if ($rememberMe) {
                    // 1. Zufälliges Token generieren
                    $rawToken = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $rawToken);
                    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

                    // 2. Token in der DB speichern
                    // (Dafür können Sie eine Query im UserModel schreiben oder direkt hier per Query-Builder)
                    $this->userModel->saveRememberToken($user['id'], $tokenHash, $expiry);

                    // 3. Cookie im Browser des Users setzen (Inhalt: user_id:raw_token)
                    $cookieValue = $user['id'] . ':' . $rawToken;

                    // Nutzt den CodeIgniter Cookie-Helper (30 Tage = 2592000 Sekunden)
                    helper('cookie');
                    set_cookie([
                        'name'   => 'fm_remember',
                        'value'  => $cookieValue,
                        'expire' => 2592000,
                        'httponly' => true, // Erhöht die Sicherheit gegen XSS-Angriffe
                        'secure' => true    // Nur über HTTPS senden (lokal evtl. anpassen, falls kein SSL)
                    ]);
                }



                return redirect()->to('flightmeet')->with('success', 'Anmeldung erfolgreich!');
            }
            return redirect()->back()->withInput()->with('error', 'E-Mail oder Passwort ist falsch.');
        }

        return view('FlightMeet/auth/login');
    }

    public function logout()
    {
        helper('cookie');
        $userId = session()->get('fm_user_id');

        if ($userId) {
            $cookie = get_cookie('fm_remember');
            if ($cookie) {
                $parts = explode(':', $cookie, 2);
                if (count($parts) === 2) {
                    $rawToken = $parts[1];
                    $tokenHash = hash('sha256', $rawToken);

                    $this->userModel->deleteRememberToken($userId, $tokenHash);
                }
            }
        }

        // Cookie im Browser löschen
        delete_cookie('fm_remember');

        // Session leeren
        session()->destroy();

        return redirect()->to('flightmeet/auth/login')->with('success', 'Abmeldung erfolgreich.');
    }

}