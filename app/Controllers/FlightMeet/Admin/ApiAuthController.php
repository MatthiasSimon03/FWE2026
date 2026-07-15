<?php

namespace App\Controllers\FlightMeet\Admin;

use App\Controllers\BaseController;
use App\Models\FlightMeet\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthController extends BaseController
{
    private string $secretKey = 'FM_SUPER_SECRET_KEY_123!';

    public function login(): ResponseInterface
    {
        $email    = trim((string) $this->request->getVar('email'));
        $password = (string) $this->request->getVar('password');

        if (empty($email) || empty($password)) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'E-Mail und Passwort müssen angegeben werden.'
            ]);
        }

        $userModel = new UserModel();
        $user = $userModel->verifyPassword($email, $password);

        // Prüfen, ob Passwörter übereinstimmen und die Rolle 'admin' ist
        if (!$user || $user['role'] !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON([
                'error' => 'Zugriff verweigert. Ungültige Anmeldedaten oder unzureichende Berechtigungen.'
            ]);
        }

        // 1. Payload mit Nutzer-ID, Rolle und Ablaufzeit (4 Stunden ab jetzt) definieren
        $payload = [
            'user_id'  => (int)$user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
            'exp'      => time() + 14400
        ];

        // 2. In JSON umwandeln und Base64URL-konform kodieren
        $jsonPayload = json_encode($payload);
        $encodedPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($jsonPayload));

        // 3. Kryptografische Signatur (HMAC-SHA256) erzeugen
        $signature = hash_hmac('sha256', $encodedPayload, $this->secretKey);

        // 4. Token zusammensetzen
        $token = $encodedPayload . '.' . $signature;

        return $this->response->setJSON([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'username' => $user['username'],
                'role'     => $user['role']
            ]
        ]);
    }
}