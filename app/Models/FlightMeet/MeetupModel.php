<?php

namespace App\Models\FlightMeet;

use CodeIgniter\Model;

class MeetupModel extends Model
{
    protected $table = 'fm_flight_meets';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'creator_id',
        'title',
        'location',
        'region',
        'meet_date',
        'meet_time',
        'experience_level',
        'max_participants',
        'description',
        'status',
        'creator_is_private',
        'longitude',
        'latitude',
    ];

    public function getMeetups(array $filters): array
    {
        $this->autoClosePastMeetups();

        $search = isset($filters['q']) ? trim((string) $filters['q']) : '';
        $region = isset($filters['region']) ? trim((string) $filters['region']) : '';
        $level = isset($filters['level']) ? trim((string) $filters['level']) : '';
        $status = $filters['status'] ?? [];
        $status = array_values(array_filter($status, static fn($s) => $s !== ''));  // Entferne leere Werte

        $builder = $this->db->table($this->table . ' fm');
        $builder
            ->select(
                'fm.id, fm.title, fm.location, fm.region, fm.description, fm.meet_date, fm.meet_time, '
                . 'fm.experience_level, fm.creator_is_private, fm.max_participants, fm.status, fm.longitude, fm.latitude, '
                . 'COUNT(p.user_id) AS participants_count'
            )
            ->join('fm_flight_meet_participants p', 'p.flight_meet_id = fm.id', 'left')
            ->groupBy('fm.id')
            ->orderBy('fm.meet_date', 'ASC');

        if ($region !== '') {
            $builder->where('fm.region', $region);
        }

        if ($level !== '') {
            $builder->where('fm.experience_level', $level);
        }

        if (is_array($status) && $status !== []) {
            $builder->whereIn('fm.status', $status);
        }

        if ($search !== '') {
            $builder
                ->groupStart()
                ->like('fm.title', $search)
                ->orLike('fm.location', $search)
                ->orLike('fm.region', $search)
                ->orLike('fm.description', $search)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function getMeetupById(int $id, ?int $currentUserId = null): ?array
    {
        $this->autoClosePastMeetups();

        $builder = $this->db->table($this->table . ' fm');
        $builder
            ->select(
                'fm.id, fm.creator_id, fm.title, fm.location, fm.region, fm.description, fm.meet_date, fm.meet_time, '
                . 'fm.experience_level, fm.creator_is_private, fm.max_participants, fm.status, fm.longitude, fm.latitude, '
                . 'COUNT(p.user_id) AS participants_count',
                false // Deaktiviert das automatische Escaping für die COUNT-Funktion
            )
            ->join('fm_flight_meet_participants p', 'p.flight_meet_id = fm.id', 'left') // Hier 'left' hinzugefügt!
            ->where('fm.id', $id)
            ->groupBy('fm.id');

        $meetup = $builder->get()->getRowArray();

        // Falls das Treffen nicht existiert (z.B. falsche ID übergeben):
        if (!$meetup) {
            // Hier Fehlerbehandlung einbauen (z. B. 404-Fehler werfen, Redirect oder return null)
            return null;
        }

        // Erst wenn sichergestellt ist, dass $meetup existiert, Teilnehmer laden:
        $participants = $this->db->table('fm_flight_meet_participants mp')
            ->select('u.id, u.username')
            ->join('fm_users u', 'u.id = mp.user_id')
            ->where('mp.flight_meet_id', $id)
            ->orderBy('u.username')
            ->get()
            ->getResultArray();

        $meetup['participants'] = $participants;
        $meetup['free_slots'] = max(0, (int)$meetup['max_participants'] - (int)$meetup['participants_count']);

        $isParticipating = false;
        if ($currentUserId !== null) {
            foreach ($participants as $p) {
                if ((int)$p['id'] === (int)$currentUserId) {
                    $isParticipating = true;
                    break;
                }
            }
        }
        $meetup['is_participating'] = $isParticipating;

        return $meetup ?: [];
    }

    // Holt alle Meetups, in denen der User angemeldet ist
    public function getUserMeetups(int $userId): array
    {
        return $this->db->table($this->table . ' fm')
            ->select('fm.id, fm.title, fm.status') // Spalte "status" hinzugefügt!
            ->join('fm_flight_meet_participants p', 'p.flight_meet_id = fm.id')
            ->where('p.user_id', $userId)
            ->orderBy('fm.meet_date', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Holt alle Flugtreffen, bei denen der User Ersteller ODER Teilnehmer ist
     */
    public function getFullUserMeetups(int $userId, array $statuses = []): array
    {
        $this->autoClosePastMeetups();

        $builder = $this->db->table($this->table . ' fm')
            ->select(
                'fm.id, fm.creator_id, fm.title, fm.location, fm.region, fm.description, fm.meet_date, fm.meet_time, '
                . 'fm.experience_level, fm.creator_is_private, fm.max_participants, fm.status, fm.longitude, fm.latitude, '
                . 'COUNT(p_all.user_id) AS participants_count, u.username as creator_name'
            )
            // JOIN 1: Prüfen, ob der User Teilnehmer ist
            ->join('fm_flight_meet_participants p_filter', 'p_filter.flight_meet_id = fm.id AND p_filter.user_id = ' . $userId, 'left')
            // JOIN 2: Alle Teilnehmer für den Counter laden
            ->join('fm_flight_meet_participants p_all', 'p_all.flight_meet_id = fm.id', 'left')
            // JOIN 3: Ersteller-Name auflösen
            ->join('fm_users u', 'u.id = fm.creator_id')
            ->groupStart()
            ->where('fm.creator_id', $userId)
            ->orWhere('p_filter.user_id', $userId)
            ->groupEnd();

        if (!empty($statuses)) {
            $builder->whereIn('fm.status', $statuses);
        }

        return $builder->groupBy('fm.id')
            ->orderBy('fm.meet_date', 'ASC')
            ->orderBy('fm.meet_time', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getFilterOptions(): array
    {
        $regions = $this->db->table($this->table)
            ->select('DISTINCT region', false)
            ->orderBy('region')
            ->get()
            ->getResultArray();

        $levels = $this->db->table($this->table)
            ->select('DISTINCT experience_level', false)
            ->orderBy('experience_level')
            ->get()
            ->getResultArray();

        $regions = array_map(static fn (array $row): string => $row['region'], $regions);
        $levels = array_map(static fn (array $row): string => $row['experience_level'], $levels);

        return [
            'regions' => $regions,
            'levels' => $levels,
        ];
    }

    public function joinMeetup(int $meetupId, int $userId): bool
    {
        $db = \Config\Database::connect();

        // 1. Prüfen, ob der Nutzer bereits teilnimmt (Doppelbuchung verhindern)
        $exists = $db->table('fm_flight_meet_participants')
                ->where('flight_meet_id', $meetupId)
                ->where('user_id', $userId)
                ->countAllResults() > 0;

        if ($exists) {
            return false;
        }

        // 2. Treffen laden und prüfen, ob es überhaupt "geplant" ist
        $meetup = $this->find($meetupId);
        if (!$meetup || $meetup['status'] !== 'geplant') {
            return false;
        }

        // 3. Aktuelle Teilnehmerzahl ermitteln
        $currentCount = $db->table('fm_flight_meet_participants')
            ->where('flight_meet_id', $meetupId)
            ->countAllResults();

        $maxParticipants = (int)($meetup['max_participants'] ?? 0);

        // 4. Prüfen, ob das Treffen bereits voll ist (sofern ein Limit > 0 gesetzt ist)
        if ($maxParticipants > 0 && $currentCount >= $maxParticipants) {
            // Falls der Status fälschlicherweise noch auf 'geplant' stand, jetzt korrigieren
            $this->update($meetupId, ['status' => 'ausgebucht']);
            return false;
        }

        // 5. Teilnehmer eintragen
        $db->table('fm_flight_meet_participants')->insert([
            'flight_meet_id' => $meetupId,
            'user_id'        => $userId,
        ]);

        // 6. Wenn das Treffen nun voll ist, Status auf 'ausgebucht' ändern
        $currentCount = $db->table('fm_flight_meet_participants')
            ->where('flight_meet_id', $meetupId)
            ->countAllResults();

        if ($currentCount >= (int)$meetup['max_participants']) {
            $this->update($meetupId, ['status' => 'ausgebucht']);
        }

        return true;
    }

    public function leaveMeetup(int $meetupId, int $userId): bool
    {
        $db = \Config\Database::connect();

        // 1. Prüfen, ob der Nutzer überhaupt angemeldet ist
        $exists = $db->table('fm_flight_meet_participants')
                ->where('flight_meet_id', $meetupId)
                ->where('user_id', $userId)
                ->countAllResults() > 0;

        if (!$exists) {
            return false;
        }

        // 2. Treffen laden
        $meetup = $this->find($meetupId);
        if (!$meetup) {
            return false;
        }

        // 3. Teilnehmer austragen
        $db->table('fm_flight_meet_participants')
            ->where('flight_meet_id', $meetupId)
            ->where('user_id', $userId)
            ->delete();

        // 4. Status-Prüfung: War das Treffen vorher ausgebucht, ändern auf geplant
        if ($meetup['status'] === 'ausgebucht') {
            $this->update($meetupId, ['status' => 'geplant']);
        }

        return true;
    }

    public function createMeetup(array $data): ?int
    {
        // Transaktion starten
        $this->db->transStart();

        // 1. Flugtreffen in die Tabelle 'fm_flight_meets' schreiben
        $meetupId = $this->insert($data);

        if ($meetupId) {
            // 2. Ersteller direkt als ersten Teilnehmer in 'fm_flight_meet_participants' eintragen
            $this->db->table('fm_flight_meet_participants')->insert([
                'flight_meet_id' => (int)$meetupId,
                'user_id'        => (int)$data['creator_id']
            ]);
        }

        // Transaktion abschließen
        $this->db->transComplete();

        // Prüfen, ob beide Querys erfolgreich waren
        if ($this->db->transStatus() === false) {
            return null; // Im Fehlerfall null zurückgeben (automatisches Rollback)
        }

        return $meetupId ? (int)$meetupId : null;
    }

    public function getActiveMeetupsCountByRegion(): array
    {
        return $this->select('region, COUNT(id) as count')
            ->whereIn('status', ['geplant', 'ausgebucht']) // Filtert nur aktive, zukünftige Treffen
            ->groupBy('region')
            ->orderBy('count', 'DESC')
            ->findAll();
    }

    public function autoClosePastMeetups(): void
    {
        $currentDateTime = date('Y-m-d H:i:s');

        // Aktualisiert alle abgelaufenen geplanten oder ausgebuchten Treffen auf "abgeschlossen"
        $this->db->table($this->table)
            ->whereIn('status', ['geplant', 'ausgebucht'])
            ->where("CONCAT(meet_date, ' ', meet_time) <", $currentDateTime)
            ->update(['status' => 'abgeschlossen']);
    }

    /**
     * Holt die 3 neuesten geplanten/ausgebuchten Flugtreffen aus Gruppen,
     * in denen der aktuelle Benutzer Mitglied ist.
     */
    public function getLatestGroupMeetups(int $userId, int $limit = 3): array
    {
        return $this->db->table($this->table . ' fm')
            ->select('fm.id, fm.title, fm.location, fm.meet_date, fm.meet_time, g.name as group_name, g.id as group_id, u.username as creator_name')
            // JOIN 1: Finde Gruppen, in denen der Ersteller des Treffens Mitglied ist
            ->join('fm_group_members creator_gm', 'creator_gm.user_id = fm.creator_id')
            // JOIN 2: Finde heraus, ob der aktuelle User in derselben Gruppe Mitglied ist
            ->join('fm_group_members user_gm', 'user_gm.group_id = creator_gm.group_id AND user_gm.user_id = ' . (int)$userId)
            // JOIN 3: Gruppen-Details für den Gruppennamen laden
            ->join('fm_groups g', 'g.id = creator_gm.group_id')
            // JOIN 4: Ersteller-Details laden (für optionale Anzeige des Namens)
            ->join('fm_users u', 'u.id = fm.creator_id')
            // Nur geplante oder ausgebuchte Treffen anzeigen
            ->whereIn('fm.status', ['geplant', 'ausgebucht'])
            // Verhindert doppelte Treffen-Einträge, falls mehrere Schnittmengen existieren
            ->groupBy('fm.id')
            // Sortiert nach Erstellungs-ID absteigend (die neuesten zuerst)
            ->orderBy('fm.id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }


}

