# Datenbank-Dokumentation: FlightMeet (`fm_`)

Diese Dokumentation beschreibt die Struktur der relationalen Datenbank für den Prototyp **FlightMeet**. Alle Tabellen wurden mit dem Präfix `fm_` versehen, um Namenskonflikte innerhalb einer bestehenden Datenbank zu vermeiden.

---

## 1. Übersicht der Beziehungen (Entity-Relationship-Konzept)

Das System besteht aus drei Hauptbereichen:
1. **Benutzer & Gruppen:** Nutzer (`fm_users`) können in Gruppen (`fm_groups`) organisiert sein (gelöst über die n:m-Tabelle `fm_group_members`).
2. **Flugtreffen:** Benutzer können Flugtreffen (`fm_flight_meets`) erstellen und sich zu diesen anmelden (gelöst über die n:m-Tabelle `fm_flight_meet_participants`).
3. **Chatsystem:** Ein zentrales Chatsystem (`fm_chat_messages`), das über optionale Fremdschlüssel Nachrichten für globale Kanäle, spezifische Gruppen oder spezifische Flugtreffen verwaltet.

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
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Erstellungsdatum der Gruppe. |

---

### 2.3. `fm_group_members` (Gruppenmitglieder)
Verknüpft Benutzer mit Gruppen (n:m-Beziehung).

| Spaltenname | Datentyp | Einschränkung | Beschreibung |
| :--- | :--- | :--- | :--- |
| `group_id` | `INT` | `PRIMARY KEY`, `FOREIGN KEY` | Referenziert `fm_groups(id)` (Löschweitergabe). |
| `user_id` | `INT` | `PRIMARY KEY`, `FOREIGN KEY` | Referenziert `fm_users(id)` (Löschweitergabe). |
| `joined_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Beitrittsdatum zur Gruppe. |

---

### 2.4. `fm_flight_meets` (Flugtreffen)
Enthält alle Details zu den geplanten Flugaktivitäten.

| Spaltenname | Datentyp                                                     | Einschränkung | Beschreibung                                                                |
| :--- |:-------------------------------------------------------------| :--- |:----------------------------------------------------------------------------|
| `id` | `INT`                                                        | `PRIMARY KEY`, `AUTO_INCREMENT` | Eindeutige ID des Flugtreffens.                                             |
| `creator_id` | `INT`                                                        | `NOT NULL`, `FOREIGN KEY` | Referenziert den Ersteller in `fm_users(id)`.                               |
 | `creator_is_private` | `BOOLEAN` | `NOT NULL` | Gibt an, ob der Ersteller anonym bleiben möchte (0: öffentlich, 1: privat). |
| `title` | `VARCHAR(150)`                                               | `NOT NULL` | Name/Titel des Flugtreffens.                                                |
| `location` | `VARCHAR(150)`                                               | `NOT NULL` | Der konkrete Flugspot (Start-/Landeplatz).                                  |
| `region` | `VARCHAR(100)`                                               | `NOT NULL` | Region zur einfacheren Filterung.                                           |
| `meet_date` | `DATE`                                                       | `NOT NULL` | Datum des Treffens.                                                         |
| `meet_time` | `TIME`                                                       | `NOT NULL` | Uhrzeit des Treffens.                                                       |
| `experience_level` | `ENUM('Einsteiger', 'Fortgeschritten', 'Profi')`             | `NOT NULL` | Mindestanforderung an die Teilnehmenden.                                    |
| `max_participants` | `INT`                                                        | `NOT NULL` | Maximale Anzahl erlaubter Piloten.                                          |
| `description` | `TEXT`                                                       | `NOT NULL` | Detailbeschreibung des Treffens.                                            |
| `status` | `ENUM('geplant', 'ausgebucht', 'abgesagt', 'abgeschlossen')` | `DEFAULT 'geplant'` | Aktueller Status des Treffens.                                              |
| `created_at` | `TIMESTAMP`                                                  | `DEFAULT CURRENT_TIMESTAMP` | Erstellungszeitpunkt.                                                       |
| `updated_at` | `TIMESTAMP`                                                  | `DEFAULT ... ON UPDATE ...` | Letzte Änderung des Treffens.                                               |

---

### 2.5. `fm_flight_meet_participants` (Teilnehmer an Treffen)
Verknüpft Benutzer mit den Flugtreffen, an denen sie teilnehmen (n:m-Beziehung).

| Spaltenname | Datentyp | Einschränkung | Beschreibung |
| :--- | :--- | :--- | :--- |
| `flight_meet_id` | `INT` | `PRIMARY KEY`, `FOREIGN KEY` | Referenziert `fm_flight_meets(id)` (Löschweitergabe). |
| `user_id` | `INT` | `PRIMARY KEY`, `FOREIGN KEY` | Referenziert `fm_users(id)` (Löschweitergabe). |
| `registered_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Zeitpunkt der Anmeldung. |

---

### 2.6. `fm_chat_messages` (Flexibles Chatsystem)
Diese Tabelle speichert alle Chat-Nachrichten des Systems. Über Fremdschlüssel und einen Check-Constraint wird gesteuert, in welchem Kontext die Nachricht gesendet wurde.

| Spaltenname | Datentyp | Einschränkung | Beschreibung |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `PRIMARY KEY`, `AUTO_INCREMENT` | Eindeutige ID der Nachricht. |
| `sender_id` | `INT` | `NOT NULL`, `FOREIGN KEY` | Der Verfasser der Nachricht (`fm_users(id)`). |
| `message_text` | `TEXT` | `NOT NULL` | Der eigentliche Textinhalt der Nachricht. |
| `group_id` | `INT` | `NULL`, `FOREIGN KEY` | Referenziert `fm_groups(id)`. |
| `flight_meet_id` | `INT` | `NULL`, `FOREIGN KEY` | Referenziert `fm_flight_meets(id)`. |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Sendezeitpunkt. |

#### Datenintegrität über CHECK-Constraint:
Ein SQL-Constraint (`chk_fm_chat_context`) stellt sicher, dass eine Nachricht immer nur einem einzigen Kontext zugeordnet ist:
* **Globaler Chat:** `group_id` und `flight_meet_id` sind beide `NULL`.
* **Gruppenchat:** `group_id` ist befüllt, `flight_meet_id` ist `NULL`.
* **Treffenchat:** `flight_meet_id` ist befüllt, `group_id` ist `NULL`.

---

## 3. Implementierungshinweise für CodeIgniter 4 und React

* **Datenabfrage (Filterung & Suche):**
  Für die Suche auf der Startseite bzw. Übersichtsseite kann eine einfache SQL `LIKE`-Abfrage auf die Felder `title`, `location`, `region` und `description` der Tabelle `fm_flight_meets` angewendet werden.
* **Teilnehmerzahlen ermitteln:**
  Die Anzahl der belegten Plätze eines Treffens sollte zur Laufzeit mittels `COUNT` auf die Tabelle `fm_flight_meet_participants` berechnet werden, anstatt sie statisch abzuspeichern.
* **Sicherheit beim Login:**
  Nutzen Sie für das Feld `password_hash` in Ihrer PHP-Logik die eingebauten Funktionen `password_hash()` und `password_verify()`.