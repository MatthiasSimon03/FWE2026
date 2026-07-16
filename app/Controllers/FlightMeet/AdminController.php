<?php

namespace App\Controllers\FlightMeet;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AdminController extends BaseController
{
    private string $secretKey = 'FM_SUPER_SECRET_KEY_123!';

    /**
     * Rendert die Mitgliederverwaltung und stellt das API-Token bereit
     */
    public function personen(): ResponseInterface
    {
        // 1. Sicherheits-Check auf Web-Ebene
        if (session()->get('fm_user_role') !== 'admin') {
            return redirect()->to('flightmeet')->with('error', 'Zugriff verweigert. Nur Administratoren gestattet.');
        }

        // 2. Zustandsloses API-Token generieren (Gültigkeit: 1 Stunde)
        $payload = [
            'user_id'  => (int)session()->get('fm_user_id'),
            'username' => session()->get('fm_username'),
            'role'     => 'admin',
            'exp'      => time() + 3600
        ];

        $jsonPayload = json_encode($payload);
        $encodedPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($jsonPayload));
        $signature = hash_hmac('sha256', $encodedPayload, $this->secretKey);

        // Das fertige Token zur Übergabe an JS
        $apiToken = $encodedPayload . '.' . $signature;

        return $this->response->setBody(view('FlightMeet/admin/personen', [
            'title'    => 'FlightMeet - Mitgliederverwaltung',
            'active'   => 'admin',
            'apiToken' => $apiToken // Token wird an die View übergeben
        ]));
    }
}