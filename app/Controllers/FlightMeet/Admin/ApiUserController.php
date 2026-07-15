<?php

namespace App\Controllers\FlightMeet\Admin;

use CodeIgniter\RESTful\ResourceController;

class ApiUserController extends ResourceController
{
    protected $modelName = 'App\Models\FlightMeet\UserModel';
    protected $format    = 'json';

    /**
     * GET /api/flightmeet/admin/users
     * Listet alle Mitglieder auf
     */
    public function index()
    {
        $users = $this->model->select('id, username, email, role, experience_level, created_at')
            ->orderBy('username', 'ASC')
            ->findAll();

        return $this->respond($users);
    }

    /**
     * PUT /api/flightmeet/admin/users/(:num)
     * Aktualisiert Username, Erfahrungslevel und Rolle eines Mitglieds komplett
     */
    public function update($id = null)
    {
        $user = $this->model->find($id);
        if (!$user) {
            return $this->failNotFound('Benutzer wurde nicht gefunden.');
        }

        // Eingabedaten auslesen
        $username        = trim((string)($this->request->getVar('username') ?? ''));
        $experienceLevel = $this->request->getVar('experience_level');
        $role            = $this->request->getVar('role');

        // 1. Pflichtfeld-Validierung
        if (empty($username) || empty($experienceLevel) || empty($role)) {
            return $this->failValidationError('Alle Felder (Benutzername, Erfahrungslevel, Rolle) sind erforderlich.');
        }

        // 2. Wertebereiche validieren
        if (!in_array($experienceLevel, ['Einsteiger', 'Fortgeschritten', 'Profi'], true)) {
            return $this->failValidationError('Ungültiges Erfahrungslevel.');
        }
        if (!in_array($role, ['user', 'admin'], true)) {
            return $this->failValidationError('Ungültige Rolle.');
        }

        // 3. Prüfen, ob der neue Username bereits vergeben ist (Double-Check)
        $existing = $this->model->where('username', $username)->where('id !=', $id)->first();
        if ($existing) {
            return $this->fail('Dieser Benutzername wird bereits von einem anderen Piloten verwendet.');
        }

        // 4. Sicherheitsregel: Ein Admin darf sich selbst über die API nicht die Admin-Rolle entziehen
        $currentAdminId = $this->request->adminUser['user_id'] ?? null;
        if ((int)$user['id'] === (int)$currentAdminId && $role !== 'admin') {
            return $this->fail('Du kannst dir deine eigene Admin-Rolle nicht selbst entziehen.');
        }

        $updateData = [
            'username'         => $username,
            'experience_level' => $experienceLevel,
            'role'             => $role
        ];

        if ($this->model->update($id, $updateData)) {
            return $this->respond([
                'success' => true,
                'message' => 'Mitglied erfolgreich aktualisiert.',
                'user'    => array_merge($user, $updateData)
            ]);
        }

        return $this->fail('Änderung der Daten fehlgeschlagen.');
    }

    /**
     * DELETE /api/flightmeet/admin/users/(:num)
     * Löscht ein Mitglied dauerhaft
     */
    public function delete($id = null)
    {
        $user = $this->model->find($id);
        if (!$user) {
            return $this->failNotFound('Benutzer wurde nicht gefunden.');
        }

        $currentAdminId = $this->request->adminUser['user_id'] ?? null;
        if ((int)$user['id'] === (int)$currentAdminId) {
            return $this->fail('Du kannst deinen eigenen Account nicht über die Schnittstelle löschen.');
        }

        if ($this->model->delete($id)) {
            return $this->respondDeleted([
                'success' => true,
                'message' => 'Benutzer wurde erfolgreich gelöscht.',
                'user_id' => $id
            ]);
        }

        return $this->fail('Löschvorgang fehlgeschlagen.');
    }
}