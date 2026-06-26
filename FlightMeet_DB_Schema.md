# Datenbank-Dokumentation: FlightMeet (`fm_`)

Diese Dokumentation beschreibt die Struktur der relationalen Datenbank für den Prototyp **FlightMeet**. Alle Tabellen wurden mit dem Präfix `fm_` versehen, um Namenskonflikte innerhalb einer bestehenden Datenbank zu vermeiden.

---

## 1. Übersicht der Beziehungen (Entity-Relationship-Konzept)

Das System besteht aus vier Hauptbereichen:
1. **Benutzer & Gruppen:** Nutzer (`fm_users`) können in Gruppen (`fm_groups`) organisiert sein (gelöst über die n:m-Tabelle `fm_group_members`). Private Gruppen nutzen `fm_group_join_requests` für Beitrittsanfragen.
2. **Flugtreffen:** Benutzer können Flugtreffen (`fm_flight_meets`) erstellen und sich zu diesen anmelden (gelöst über die n:m-Tabelle `fm_flight_meet_participants`).
3. **Chatsystem:** Ein zentrales Chatsystem (`fm_chat_messages`), das über optionale Fremdschlüssel Nachrichten für globale Kanäle, spezifische Gruppen oder spezifische Flugtreffen verwaltet.
4. **Authentifizierung:** Token-basiertes „Remember Me" System (`fm_remember_tokens`).

---

## 2. Tabellendetails

### 2.1. `fm_users` (Benutzer)
Speichert die Registrierungs- und Profildaten der Pilotinnen und Piloten.

| Spaltenname | Datentyp | Einschränkung | Beschreibung |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Eindeutige ID des Nutzers. |
| `username` | `VARCHAR(50)` | `NOT NULL`, `UNIQUE` | Eindeutiger Benutzername. |
| `email` | `VARCHAR(100)` | `NOT NULL`, `UNIQUE` | Eindeutige E-Mail-Adresse. |
| `password_hash` | `VARCHAR(255)` | `NOT NULL` | Gehashtes Passwort für die Authentifizierung. |
| `role` | `ENUM('user', 'admin')` | `DEFAULT 'user'` | Benutzerrolle für Berechtigungen. |
| `experience_level` | `ENUM('Einsteiger', 'Fortgeschritten', 'Profi')` | `NOT NULL` | Vordefiniertes Erfahrungslevel des Piloten. |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Registrierungszeitpunkt. |
| `updated_at` | `TIMESTAMP` | `DEFAULT ... ON UPDATE ...` | Letzte Profilaktualisierung. |

---

### 2.2. `fm_groups` (Gruppen)
Verwaltet die von Nutzern erstellten Communities oder Regionalgruppen.

| Spaltenname | Datentyp | Einschränkung | Beschreibung |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Eindeutige ID der Gruppe. |
| `name` | `VARCHAR(100)` | `NOT NULL`, `UNIQUE` | Name der Gruppe. |
| `description` | `TEXT` | `NULL` | Optionale Beschreibung des Gruppenzwecks. |
| `rules` | `TEXT` | `NULL` | Freitext mit Regeln und Richtlinien der Gruppe. |
| `visibility` | `ENUM('open', 'private')` | `NOT NULL`, `DEFAULT 'open'` | Sichtbarkeitsstatus: 'open' (direkter Beitritt) oder 'private' (Anfrage erforderlich). |
| `region` | `VARCHAR(100)` | `NULL` | Regionale Zuordnung / Fokusregion der Gruppe. |
| `base_location` | `VARCHAR(150)` | `NULL` | Basis-Standort der Gruppe (z.B. Startplatz). |
| `latitude` | `DECIMAL(9, 6)` | `NULL` | Latitude-Koordinate der Basis-Location. |
| `longitude` | `DECIMAL(9, 6)` | `NULL` | Longitude-Koordinate der Basis-Location. |
| `created_by` | `INT` | `NOT NULL`, `FOREIGN KEY` | Referenziert den Owner (`fm_users(id)`, ON DELETE RESTRICT). |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Erstellungsdatum der Gruppe. |
| `updated_at` | `TIMESTAMP` | `DEFAULT ... ON UPDATE ...` | Letzte Änderung der Gruppe. |

---

### 2.3. `fm_group_members` (Gruppenmitglieder)
Verknüpft Benutzer mit Gruppen und definiert deren Rollen (n:m-Beziehung mit Rollen).

| Spaltenname | Datentyp | Einschränkung | Beschreibung |
| :--- | :--- | :--- | :--- |
| `group_id` | `INT` | `PRIMARY KEY`, `FOREIGN KEY` | Referenziert `fm_groups(id)` (CASCADE DELETE). |
| `user_id` | `INT` | `PRIMARY KEY`, `FOREIGN KEY` | Referenziert `fm_users(id)` (CASCADE DELETE). |
| `role` | `ENUM('owner', 'admin', 'member')` | `NOT NULL`, `DEFAULT 'member'` | Rolle des Mitglieds in der Gruppe. |
| `joined_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Beitrittsdatum zur Gruppe. |

**Constraint:** Pro Gruppe darf es nur genau einen `owner` geben.

---

### 2.4. `fm_group_join_requests` (Beitrittsanfragen für private Gruppen)
Speichert Anfragen von Benutzern zum Beitritt in private Gruppen.

| Spaltenname | Datentyp | Einschränkung | Beschreibung |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Eindeutige ID der Anfrage. |
| `group_id` | `INT` | `NOT NULL`, `FOREIGN KEY` | Referenziert `fm_groups(id)` (CASCADE DELETE). |
| `user_id` | `INT` | `NOT NULL`, `FOREIGN KEY` | Referenziert `fm_users(id)` (CASCADE DELETE). |
| `status` | `ENUM('pending', 'accepted', 'rejected', 'cancelled')` | `NOT NULL`, `DEFAULT 'pending'` | Aktueller Status der Anfrage. |
| `message` | `TEXT` | `NULL` | Optionale Nachricht des Antragstellers. |
| `requested_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Zeitstempel der Anfrage. |
| `handled_by` | `INT` | `NULL`, `FOREIGN KEY` | Referenziert den Admin/Owner, der die Anfrage bearbeitet hat (`fm_users(id)`, ON DELETE SET NULL). |
| `handled_at` | `DATETIME` | `NULL` | Zeitstempel der Bearbeitung (Annahme oder Ablehnung). |

**Constraint:** 
- UNIQUE(group_id, user_id) – maximal ein Eintrag pro User pro Gruppe
- Bei `join()` wird Eintrag erstellt oder aktualisiert (Status von `rejected` zurück zu `pending`)

---

### 2.5. `fm_flight_meets` (Flugtreffen)
Enthält alle Details zu den geplanten Flugaktivitäten.

| Spaltenname | Datentyp                                                     | Einschränkung                   | Beschreibung                                                                |
| :--- |:-------------------------------------------------------------|:--------------------------------|:----------------------------------------------------------------------------|
| `id` | `INT`                                                        | `PRIMARY KEY`, `AUTO_INCREMENT` | Eindeutige ID des Flugtreffens.                                             |
| `creator_id` | `INT`                                                        | `NOT NULL`, `FOREIGN KEY`       | Referenziert den Ersteller in `fm_users(id)`.                               |
| `creator_is_private` | `BOOLEAN`                                                    | `NOT NULL`                      | Gibt an, ob der Ersteller anonym bleiben möchte (0: öffentlich, 1: privat). |
| `title` | `VARCHAR(150)`                                               | `NOT NULL`                      | Name/Titel des Flugtreffens.                                                |
| `location` | `VARCHAR(150)`                                               | `NOT NULL`                      | Der konkrete Flugspot (Start-/Landeplatz).                                  |
| `region` | `VARCHAR(100)`                                               | `NOT NULL`                      | Region zur einfacheren Filterung.                                           |
| `meet_date` | `DATE`                                                       | `NOT NULL`                      | Datum des Treffens.                                                         |
| `meet_time` | `TIME`                                                       | `NOT NULL`                      | Uhrzeit des Treffens.                                                       |
| `experience_level` | `ENUM('Einsteiger', 'Fortgeschritten', 'Profi')`             | `NOT NULL`                      | Mindestanforderung an die Teilnehmenden.                                    |
| `max_participants` | `INT`                                                        | `NOT NULL`                      | Maximale Anzahl erlaubter Piloten.                                          |
| `description` | `TEXT`                                                       | `NOT NULL`                      | Detailbeschreibung des Treffens.                                            |
| `status` | `ENUM('geplant', 'ausgebucht', 'abgesagt', 'abgeschlossen')` | `DEFAULT 'geplant'`             | Aktueller Status des Treffens.                                              |
| `created_at` | `TIMESTAMP`                                                  | `DEFAULT CURRENT_TIMESTAMP`     | Erstellungszeitpunkt.                                                       |
| `updated_at` | `TIMESTAMP`                                                  | `DEFAULT ... ON UPDATE ...`     | Letzte Änderung des Treffens.                                               |
| `longitude` | `DECIMAL(9, 6)`                                              | `NULL`                          | Längengrad des Flugspots.                                                   |
| `latitude` | `DECIMAL(9, 6)`                                              | `NULL`                          | Breitengrad des Flugspots.                                                  |

---

### 2.6. `fm_flight_meet_participants` (Teilnehmer an Treffen)
Verknüpft Benutzer mit den Flugtreffen, an denen sie teilnehmen (n:m-Beziehung).

| Spaltenname | Datentyp | Einschränkung | Beschreibung |
| :--- | :--- | :--- | :--- |
| `flight_meet_id` | `INT` | `PRIMARY KEY`, `FOREIGN KEY` | Referenziert `fm_flight_meets(id)` (CASCADE DELETE). |
| `user_id` | `INT` | `PRIMARY KEY`, `FOREIGN KEY` | Referenziert `fm_users(id)` (CASCADE DELETE). |
| `registered_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Zeitpunkt der Anmeldung. |

---

### 2.7. `fm_chat_messages` (Flexibles Chatsystem)
Diese Tabelle speichert alle Chat-Nachrichten des Systems. Über Fremdschlüssel wird gesteuert, in welchem Kontext die Nachricht gesendet wurde.

| Spaltenname | Datentyp | Einschränkung | Beschreibung |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Eindeutige ID der Nachricht. |
| `sender_id` | `INT` | `NOT NULL`, `FOREIGN KEY` | Der Verfasser der Nachricht (`fm_users(id)`). |
| `message_text` | `TEXT` | `NOT NULL` | Der eigentliche Textinhalt der Nachricht. |
| `group_id` | `INT` | `NULL`, `FOREIGN KEY` | Referenziert `fm_groups(id)` (Gruppenchat). |
| `flight_meet_id` | `INT` | `NULL`, `FOREIGN KEY` | Referenziert `fm_flight_meets(id)` (Treffen-Chat). |
| `recipient_id` | `INT` | `NULL`, `FOREIGN KEY` | Optionaler Empfänger für Direktnachrichten (Referenz auf `fm_users(id)`). |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Sendezeitpunkt. |

**Datenintegrität:**
Eine Nachricht nutzt genau einen dieser Kontexte:
- **Globaler Chat:** `group_id = NULL`, `flight_meet_id = NULL`, `recipient_id = NULL`
- **Gruppenchat:** `group_id` ist befüllt, `flight_meet_id = NULL`, `recipient_id = NULL`
- **Treffen-Chat:** `flight_meet_id` ist befüllt, `group_id = NULL`, `recipient_id = NULL`
- **Direktnachricht:** `recipient_id` is befüllt, `group_id = NULL`, `flight_meet_id = NULL`

---

### 2.8. `fm_remember_tokens` (Token für "Angemeldet bleiben")
Diese Tabelle speichert die Tokens für die Funktion "Angemeldet bleiben" (Remember Me).

| Spaltenname  | Datentyp | Einschränkung | Beschreibung |
|:-------------| :--- | :--- | :--- |
| `id`         | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Eindeutige ID des Tokens. |
| `user_id`    | `INT` | `NOT NULL`, `FOREIGN KEY` | Referenziert den Nutzer in `fm_users(id)`. |
| `token_hash` | `VARCHAR(255)` | `NOT NULL` | Gehashtes Token für die Authentifizierung. |
| `expires_at` | `DATETIME` | `NOT NULL` | Ablaufzeitpunkt des Tokens. |

---

## 3. Implementierungshinweise für CodeIgniter 4

### 3.1 Gruppen und Mitgliedschaft
* **Gruppenprofil-Abfrage:** Nutzen Sie einen JOIN von `fm_groups` und `fm_group_members` mit Rollen-Filter
* **Gruppenflüge in privaten Gruppen:** Nutzen Sie einen JOIN von `fm_flight_meets`, `fm_group_members` (gefiltert nach `creator_id`) und prüfen Sie, ob der Ersteller aktuell Mitglied ist
* **Private Ersteller in Gruppen:** Falls `creator_is_private = 1`, geben Sie den Namen NICHT aus, sondern zeigen Sie „📍 Privater Ersteller"
* **Flug-Filterung:** Standardmäßig `status IN ('geplant', 'ausgebucht')`; optionaler Tab für `status IN ('abgeschlossen', 'abgesagt')`

### 3.2 Beitrittsanfragen (private Gruppen)
* **Anfrage stellen:** Wenn `visibility = 'private'` → Eintrag in `fm_group_join_requests` mit `status = 'pending'`
* **Anfrage annehmen:** Aktualisiere Status auf `'accepted'`, erstelle Eintrag in `fm_group_members` mit `role = 'member'`
* **Anfrage ablehnen:** Aktualisiere Status auf `'rejected'` (Zeile bleibt zur historischen Referenz)
* **Erneut anfragen:** Falls `status = 'rejected'`, UPDATE auf `status = 'pending'` (selbe Zeile)

### 3.3 Owner-Transfer
* **Owner übertragen:** Transaction starten, `role` des neuen Owners auf `'owner'` setzen, alten Owner auf `'admin'` herabstufen, alte Admins belassen
* **Fehler-Szenario:** Falls mehr als ein Owner existiert (Datenbankfehler), nutzen Sie einen UPDATE mit `WHERE role = 'owner' LIMIT 1` zum Beheben

### 3.4 Datenabfrage
* **Suche in Gruppen:** LIKE-Abfrage auf `name`, `description`, `rules`, `region`
* **Flug-Teilnehmerzahlen:** Zur Laufzeit mit COUNT auf `fm_flight_meet_participants` ermitteln
* **Karten-Daten:** SELECT mit `WHERE latitude IS NOT NULL AND longitude IS NOT NULL`

### 3.5 Sicherheit
* Nutzen Sie für das Feld `password_hash` in Ihrer PHP-Logik die eingebauten Funktionen `password_hash()` und `password_verify()`
* Alle schreibenden Zugriffe auf Gruppen müssen serverseitig autorisiert sein (Role-Check)
* Nur Gruppe-Mitglieder dürfen Gruppenflüge sehen
* Nur Owner/Admin dürfen Anfragen verwalten
