<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class FlightMeetAdminFilter implements FilterInterface
{
    // Geheimer kryptografischer Schlüssel (Muss mit dem im AuthController übereinstimmen)
    private string $secretKey = 'FM_SUPER_SECRET_KEY_123!';

    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Authorization-Header auslesen
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Kein API-Token im Authorization-Header übergeben. Format: Bearer <token>']);
        }

        $token = $matches[1];

        // 2. Token in Payload (Daten) und Signatur zerlegen
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Ungültiges Token-Format.']);
        }

        list($encodedPayload, $signature) = $parts;

        // 3. Kryptografischen Echtheitsabgleich durchführen (Signatur-Verifikation)
        $expectedSignature = hash_hmac('sha256', $encodedPayload, $this->secretKey);
        if (!hash_equals($expectedSignature, $signature)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Kryptografische Signatur des Tokens ist ungültig (Manipulationsschutz).']);
        }

        // 4. Payload aus dem Base64-Format dekodieren
        $jsonPayload = base64_decode(str_replace(['-', '_'], ['+', '/'], $encodedPayload));
        $payload = json_decode($jsonPayload, true);

        // 5. Ablaufzeit des Tokens prüfen
        if (!$payload || !isset($payload['exp']) || time() > $payload['exp']) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Das API-Token ist abgelaufen. Bitte neu anmelden.']);
        }

        // 6. Rollenprüfung: Ist der Token-Inhaber wirklich ein Admin?
        if (($payload['role'] ?? '') !== 'admin') {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'Zugriff verweigert. Keine Administratorrechte.']);
        }

        // Token-Daten für die spätere Nutzung im Controller an das Request-Objekt hängen
        $request->adminUser = $payload;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nicht benötigt
    }
}