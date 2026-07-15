<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class FlightMeetAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('cookie');
        $session = session();

        // Fall 1: User hat keine aktive Session, aber wir prüfen das Cookie
        if (!$session->get('fm_user_id')) {
            $cookie = get_cookie('fm_remember');

            if ($cookie) {
                // Cookie-Format ist "user_id:raw_token"
                $parts = explode(':', $cookie, 2);

                if (count($parts) === 2) {
                    $userId = $parts[0];
                    $rawToken = $parts[1];
                    $tokenHash = hash('sha256', $rawToken);

                    $userModel = new \App\Models\FlightMeet\UserModel();
                    $tokenData = $userModel->getValidRememberToken($userId, $tokenHash);

                    if ($tokenData) {
                        // Token ist gültig! Wir holen die Benutzerdaten und erstellen die Session neu
                        $userModel = new \App\Models\FlightMeet\UserModel();
                        $user = $userModel->find($userId); // Passen Sie dies an Ihre Find-Methode im Model an

                        if ($user) {
                            $session->set([
                                'fm_user_id' => $user['id'],
                                'fm_username' => $user['username'],
                                'fm_email' => $user['email'],
                                'fm_role' => $user['role'] ?? 'user',
                                'fm_user_role' => $user['role'] ?? 'user',
                                'fm_experience_level' => $user['experience_level'] ?? '',
                            ]);

                            // Erfolgreich eingeloggt, wir können die Filter-Prüfung beenden
                            return;
                        }
                    }
                }
            }

            // Fall 2: Keine Session und kein (gültiges) Cookie -> Umleitung zum Login
            return redirect()
                ->to('flightmeet/auth/login')
                ->with('error', 'Melden Sie sich zunächst an.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}