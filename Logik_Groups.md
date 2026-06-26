# FlightMeet – Spezifikation für Gruppenlogik

Diese Datei definiert die fachlichen Regeln und die technische Implementierungslogik für Gruppen im FlightMeet-System.

---

## 1. Ziel

Gruppen dienen dazu, Flugtreffen gemeinschaftlich zu organisieren. Eine Gruppe besteht aus genau einem Owner, mehreren Admins und mehreren Members. Gruppen können öffentlich oder privat sein. In Gruppen werden Flugtreffen von Gruppenmitgliedern angezeigt.

---

## 2. Rollenmodell

### 2.1 Rollen
Jedes Gruppenmitglied hat genau eine Rolle:

- `owner`
- `admin`
- `member`

### 2.2 Bedeutung der Rollen

#### Owner
Der Owner ist der Ersteller der Gruppe oder der Nutzer, dem der Ownerstatus übertragen wurde.

Der Owner ist die höchste Instanz und hat folgende Rechte:
- Gruppe bearbeiten
- Gruppe löschen
- Admins ernennen
- Admins entfernen und herabstufen
- Member entfernen
- Ownerstatus an einen anderen Nutzer übertragen (und erhält danach selbst die Rolle `admin`)

Der Owner ist unersetzbar und kann nicht von anderen Mitgliedern entfernt werden.

#### Admin
Admins werden vom Owner oder von anderen Admins ernannt.

Rechte des Admins:
- Member entfernen (nicht aber den Owner)
- andere Admins ernennen (darf aber nur Member zu Admin befördern)
- Anfragen für private Gruppen annehmen oder ablehnen
- Gruppeninhalte verwalten (soweit vom Owner erlaubt)

Einschränkungen des Admins:
- Ein Admin darf **nur Member zu Admin befördern**, nicht aber die Rolle eines anderen Admins ändern
- Ein Admin darf keinen Owner entfernen oder herabstufen
- Ein Admin darf den Ownerstatus nicht selbst übernehmen und nicht übertragen
- Ein Admin darf den Owner nicht löschen
- Ein Admin darf sich selbst nicht entfernen

#### Member
Members sind normale Gruppenmitglieder.

Rechte des Members:
- Gruppe ansehen
- offene Gruppe direkt betreten
- private Gruppe per Anfrage betreten
- Grup verlassen (außer, wenn der Member gleichzeitig Owner ist)
- Gruppenflüge ansehen, soweit Mitgliedschaft besteht

---

## 3. Gruppenstatus

Eine Gruppe hat genau einen Sichtbarkeitsstatus:

- `open`: Offene Gruppen
- `private`: Private Gruppen

### 3.1 Open
Offene Gruppen können ohne Anfrage betreten werden.

Regeln:
- Ein angemeldeter Nutzer darf einer offenen Gruppe direkt beitreten
- Nach dem Beitritt wird der Nutzer direkt als `member` eingetragen
- Es gibt kein Genehmigungsverfahren

### 3.2 Private
Private Gruppen erfordern eine Beitrittsanfrage.

Regeln:
- Ein Nutzer darf nur eine offene Anfrage (`pending`) gleichzeitig pro Gruppe haben
- Solange eine Anfrage im Status `pending` existiert, darf keine weitere Anfrage gestellt werden
- Wurde eine Anfrage abgelehnt (`rejected`), darf der Nutzer sofort erneut eine Anfrage stellen
- **Nur offene Anfragen blockieren neue Anfragen**
- Nach Annahme der Anfrage wird der Nutzer direkt als `member` eingetragen
- Bei Ablehnung wird der Anfrageeintrag auf `rejected` gesetzt (nicht gelöscht)

---

## 4. Owner-Regeln

### 4.1 Erstellung
Der Ersteller einer Gruppe wird automatisch `owner`.

### 4.2 Owner übertragen
Nur der aktuelle Owner darf den Ownerstatus an einen anderen Nutzer übertragen.

Regeln bei Owner-Transfer:
- **neuer Nutzer erhält die Rolle `owner`**
- **alter Owner wird automatisch zu `admin` herabgestuft**
- existierende Admins bleiben Admins
- Es existiert danach genau ein Owner pro Gruppe
- Der alte Owner kann die Rolle des admins normal nicht mehr ändern (es sei denn, der neue Owner gibt ihm wieder Owner-Rechte)

### 4.3 Selbstschutz des Owners
Der Owner darf sich selbst nicht von der Gruppe entfernen.

Regel:
- Der Owner kann die Gruppe nur verlassen, wenn er den Ownerstatus vorher auf einen anderen Nutzer überträgt
- Dies verhindert, dass eine Gruppe ohne Owner zurückbleibt

### 4.4 Gruppe löschen
Nur der Owner darf die Gruppe löschen.

---

## 5. Admin-Regeln

### 5.1 Admin ernennen
Sowohl Owner als auch Admins dürfen neue Admins ernennen.

**Genaue Regeln:**
- **Admin darf nur Member zu Admin befördern**, nicht aber:
    - andere Admins erneut bestätigen
    - Rollen von anderen Admins ändern
    - Member zu Owner machen
- **Owner darf Member zu Admin machen und Admin zu Member herabstufen oder zu Admin „erneut" bestätigen**
- **Owner ist die höchste Instanz und kann alle Rollen verwalten**

### 5.2 Mehrere Admins
Es dürfen mehrere Admins gleichzeitig existieren.

### 5.3 Rollen entfernen / herabstufen
Ein Owner darf:
- Admins entfernen / zu Member herabstufen
- Member entfernen

Ein Admin darf:
- Member entfernen, aber nicht:
    - den Owner entfernen
    - andere Admins entfernen
    - den Ownerstatus anfechten

---

## 6. Mitgliedschaftsregeln

### 6.1 Offene Gruppen
Bei offenen Gruppen (`visibility = open`) ist kein Antrag erforderlich.

Ablauf:
1. Nutzer klickt auf „Beitreten"
2. System prüft, ob er bereits Mitglied ist
3. Wenn nicht Mitglied → Nutzer wird direkt als `member` eingetragen
4. Bestätigung / Willkommensnachricht wird angezeigt

### 6.2 Private Gruppen
Bei privaten Gruppen (`visibility = private`) ist eine Anfrage erforderlich.

Ablauf:
1. Nutzer klickt auf „Anfrage stellen"
2. System prüft, ob bereits eine offene (`pending`) Anfrage existiert
3. Falls ja → Fehlermeldung „Du hast bereits eine Anfrage"
4. Falls nein → neue Anfrage mit Status `pending` wird angelegt
5. Admin oder Owner kann Anfrage annehmen oder ablehnen

### 6.3 Anfragen (für private Gruppen)

#### Status einer Anfrage
- `pending`: Anfrage wartet auf Bearbeitung
- `accepted`: Anfrage wurde angenommen → Nutzer ist jetzt Member
- `rejected`: Anfrage wurde abgelehnt → Nutzer darf später erneut anfragen
- `cancelled`: Anfrage wurde vom Nutzer selbst storniert

#### Regeln
- Es darf **nur eine aktive `pending` Anfrage pro Nutzer und Gruppe** existieren
- **Abgelehnte (`rejected`) Anfragen blockieren NICHT neue Anfragen**
- Ein Nutzer mit `rejected`-Status darf sofort erneut anfragt stellen
- Nach Annahme wird der Nutzer sofort in `fm_group_members` als `member` eingetragen
- Der Anfrageeintrag wird auf `accepted` gesetzt
- **Jeder Admin und der Owner darf offene Anfragen sehen und bearbeiten**
- Bei Ablehnung wird der Anfrageeintrag auf `rejected` gesetzt (nicht gelöscht)

---

## 7. Sichtbarkeit von Gruppenflügen

### 7.1 Grundregel
In einer Gruppe werden Flugtreffen angezeigt, deren Ersteller aktuell Mitglied der Gruppe ist.

Regel:
- **Ist der Ersteller Mitglied der Gruppe → Flug sichtbar**
- **Ist der Ersteller nicht mehr Mitglied der Gruppe → Flug nicht mehr sichtbar**
- Die Sichtbarkeit ist an die **aktuelle Mitgliedschaft des Erstellers** gekoppelt

### 7.2 Private Ersteller
Wenn der Ersteller eines Flugtreffens sein Profil auf privat gesetzt hat (`creator_is_private = true`), wird das Treffen trotzdem in Gruppen angezeigt, sofern der Ersteller Gruppenmitglied ist.

Regel:
- Das Flugtreffen bleibt vollständig sichtbar (Titel, Ort, Datum, Uhrzeit, Beschreibung, etc.)
- **Der Name des Erstellers wird NICHT angezeigt**
- Stattdessen wird ein neutraler Hinweis verwendet, z. B.:
    - „📍 Privater Ersteller"
    - oder kein Name, sondern nur ein Icon
- Die Teilnehmerzahl wird weiterhin angezeigt

### 7.3 Anzeige in der Gruppe
Im Gruppenprofil werden Flüge in verschiedenen Status angezeigt:
- geplante und ausgebuchte Flüge
- historische Flüge (optional)

---

## 8. Anzeige von Flügen in Gruppen

### 8.1 Standardanzeige
Standardmäßig werden **geplante und ausgebuchte Flüge** angezeigt.

Status:
- `status IN ('geplant', 'ausgebucht')`

### 8.2 Historische Flüge
Es soll möglich sein, ältere Flüge anzuschauen.

Regeln:
- Historische Flüge sind standardmäßig ausgeblendet
- Über einen Filter oder ein Tab können sie eingeblendet werden
- Sichtbare historische Status:
    - `status IN ('abgeschlossen', 'abgesagt')`

### 8.3 Gruppenkontext
Im Gruppendetail soll es mindestens zwei separate Ansichten / Tabs geben:
- **Geplante Flüge**: `status IN ('geplant', 'ausgebucht')`
- **Vergangene Flüge**: `status IN ('abgeschlossen', 'abgesagt')`

Benutzer können zwischen diesen Tabs wechseln.

---

## 9. Gruppenkarte und Kalender

### 9.1 Gruppenkarte
Die Gruppenkarte zeigt alle geplanten und ausgebuchten Gruppenflüge als Pins auf einer Karte.

Regeln:
- **Nur Flüge mit gültigen Koordinaten (`latitude` und `longitude`) werden gepinnt**
- Flüge ohne Koordinaten werden NICHT auf der Karte angezeigt
- Standardmäßig werden nur geplante und ausgebuchte Flüge angezeigt
- Bei Klick auf einen Pin öffnet sich das Flugdetail
- Pin-Tooltip zeigt: Titel, Datum, Uhrzeit, Ort (ggf. ohne Ersteller-Name, wenn privat)

### 9.2 Kalenderansicht
Die Kalenderansicht zeigt Gruppenflüge nach Datum in einer Monatsansicht.

Regeln:
- Standardmäßig werden nur geplante und ausgebuchte Flüge angezeigt
- Historische Flüge können optional über einen Filter/Tab sichtbar gemacht werden
- Ein Klick auf einen Termin zeigt die Flüge für diesen Tag
- Ein Klick auf einen Flug öffnet das Flugdetail

---

## 10. Empfohlene Datenbankerweiterungen

### 10.1 `fm_groups`
Empfohlene Felder:
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `name` (VARCHAR(100), NOT NULL, UNIQUE)
- `description` (TEXT, NULL)
- `rules` (TEXT, NULL)
- `visibility` (ENUM('open', 'private'), NOT NULL, DEFAULT 'open')
- `region` (VARCHAR(100), NULL)
- `base_location` (VARCHAR(150), NULL)
- `latitude` (DECIMAL(9, 6), NULL)
- `longitude` (DECIMAL(9, 6), NULL)
- `created_by` (INT, NOT NULL, FOREIGN KEY → `fm_users(id)`)
- `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, DEFAULT ... ON UPDATE ...)

### 10.2 `fm_group_members`
Empfohlene Felder:
- `group_id` (INT, PRIMARY KEY, FOREIGN KEY → `fm_groups(id)`, CASCADE DELETE)
- `user_id` (INT, PRIMARY KEY, FOREIGN KEY → `fm_users(id)`, CASCADE DELETE)
- `role` (ENUM('owner', 'admin', 'member'), NOT NULL, DEFAULT 'member')
- `joined_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

Constraint:
- UNIQUE(group_id, user_id)
- Pro Gruppe darf es nur genau einen Owner geben (optional über Trigger sichern)

### 10.3 `fm_group_join_requests`
Empfohlene Felder:
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `group_id` (INT, NOT NULL, FOREIGN KEY → `fm_groups(id)`, CASCADE DELETE)
- `user_id` (INT, NOT NULL, FOREIGN KEY → `fm_users(id)`, CASCADE DELETE)
- `status` (ENUM('pending', 'accepted', 'rejected', 'cancelled'), NOT NULL, DEFAULT 'pending')
- `message` (TEXT, NULL)
- `requested_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- `handled_by` (INT, NULL, FOREIGN KEY → `fm_users(id)`)
- `handled_at` (DATETIME, NULL)

Constraint:
- UNIQUE(group_id, user_id, status) mit Fokus auf `pending` (maximal eine `pending` pro Nutzer und Gruppe)

---

## 11. Technische Regeln für die Implementierung

### 11.1 Controller
Der Gruppencontroller soll mindestens folgende Funktionen enthalten:

**Lesezugriff (öffentlich oder für Mitglieder):**
- `index()` – Gruppenübersicht mit Such-/Filterfunktion
- `detail($id)` – Gruppenprofil inkl. Flüge, Mitglieder, Karte, Kalender
- `meetups($id)` – Flüge der Gruppe (mit optionalen Tabs: geplant / vergangen)
- `members($id)` – Mitgliederliste (optional)

**Schreibzugriff (Login erforderlich):**
- `create()` – Gruppe anlegen (GET: Formular, POST: speichern)
- `edit($id)` – Gruppe bearbeiten (GET: Formular, POST: speichern) – nur Owner
- `delete($id)` – Gruppe löschen – nur Owner
- `join($id)` – offene Gruppe betreten (POST)
- `leave($id)` – Gruppe verlassen (POST) – nicht wenn Owner (außer nach Transfer)
- `requestJoin($id)` – private Gruppe anfragen (POST)
- `approveRequest($requestId)` – Anfrage annehmen (POST) – nur Owner/Admin
- `rejectRequest($requestId)` – Anfrage ablehnen (POST) – nur Owner/Admin
- `promoteToAdmin($groupId, $userId)` – Admin ernennen (POST) – nur Owner/Admin
- `demoteFromAdmin($groupId, $userId)` – Admin herabstufen (POST) – nur Owner
- `transferOwner($groupId, $userId)` – Owner übertragen (POST) – nur Owner
- `removeMember($groupId, $userId)` – Member entfernen (POST) – nur Owner/Admin

### 11.2 Autorisierung
Alle schreibenden Aktionen müssen serverseitig geprüft werden. Die Prüfungen müssen hart in den Controllern oder in einem UserPolicy-Service erfolgen.

**Checkliste für jede Aktion:**
- existiert die Gruppe?
- ist der Nutzer angemeldet?
- welche Rolle hat der Nutzer in der Gruppe?
    - Owner → alle Rechte
    - Admin → eingeschränkte Rechte
    - Member → keine Verwaltungsrechte
    - nicht Member → nur lesen (und ggf. beitreten / anfragen)
- darf der aktuelle Nutzer diese Aktion durchführen?

**Beispiele:**
- Nur Owner darf Gruppe löschen
- Nur Owner darf Owner übertragen
- Owner oder Admin darf Admin ernennen (aber Admin nur zu Member, nicht zu Admin)
- Admin darf Member entfernen, aber nicht den Owner
- Nur Mitglieder dürfen Gruppenflüge sehen

### 11.3 Fehlerbehandlung
Bei Autorisierungsfehlern soll das System:
- 403 Forbidden zurückgeben (Aktion nicht erlaubt)
- oder 404 Not Found zurückgeben (Gruppe / Anfrage existiert nicht)
- Keine internen Informationen in Fehlermeldungen preisgeben

### 11.4 Frontend
Die Darstellung soll sich an der vorhandenen FlightMeet-Optik orientieren:
- Layout aus `app/Views/FlightMeet/layout.php`
- Karten-/Card-Design analog zu `meetups.php`, `meetupDetail.php`
- Buttons und Formularstruktur konsistent zu bestehenden Views
- Icons für Rollen (Owner, Admin, Member)
- Farbkodierung oder visuelle Unterscheidung für Status (open/private, geplant/vergangen)

---

## 12. Empfohlene Implementierungsreihenfolge

1. **Datenbankmigrationen**: `fm_groups`, `fm_group_members`, `fm_group_join_requests`
2. **Models**: `GroupModel`, `GroupMemberModel`, `GroupJoinRequestModel`
3. **Policy/Service**: Zentrales Service für Berechtigungsprüfungen
4. **Controller Grundgerüst**: `index()`, `detail()`, `create()`, `edit()`, `delete()`
5. **Mitgliedschaft**: `join()`, `leave()` für offene Gruppen
6. **Anfrage-Workflow**: `requestJoin()`, `approveRequest()`, `rejectRequest()` für private Gruppen
7. **Rollen-Management**: `promoteToAdmin()`, `demoteFromAdmin()`, `transferOwner()`, `removeMember()`
8. **Gruppenflüge-Logik**: Query für Flüge basierend auf Mitgliedschaft, Ersteller, Status
9. **Views**: Gruppenübersicht, Gruppenprofil, Flühe-Tab, Karte, Kalender
10. **Dokumentation & Tests**: Feature- und Unit-Tests, Dokumentation

---

## 13. Besondere Fälle und Sicherheit

### 13.1 Datenintegrität
- Eine Gruppe **darf immer nur einen Owner haben**
- Dies sollte durch Business-Logik UND idealer Weise durch einen DB-Trigger erzwungen werden
- Bei Owner-Transfer: Transaktion verwenden, um Konsistenz zu sichern

### 13.2 Cascade Delete
- Wenn eine Gruppe gelöscht wird (Gelegenheit für den Owner), sollen auch:
    - Alle Mitgliedschaften (`fm_group_members`) gelöscht werden
    - Alle Anfragen (`fm_group_join_requests`) gelöscht werden
    - Dies sollte durch `CASCADE DELETE` in der Datenbank erzwungen werden

### 13.3 Anfragen-Sicherheit
- Ein Nutzer darf sich selbst keine Anfrage stellen
- Ein bereits Mitglieder-Nutzer darf nicht erneut anfragen
- Spam-Schutz: optional maximal N Anfragen pro Stunde pro Nutzer

### 13.4 Flug-Sichtbarkeit in Gruppen
- Flüge werden **nur wenn der Ersteller aktuell Mitglied ist** angezeigt
- Falls ein Ersteller die Gruppe verlässt, verschwinden seine Flüge sofort aus der Gruppensicht
- Dies saldwird mit einem **JOIN** über `fm_group_members` gelöst, nicht mit statischem Cache

---

## 14. Offene Punkte für spätere Erweiterung

- Sollen es Einladungslinks für private Gruppen geben?
- Soll ein Admin auch Gruppenregeln / Beschreibung ändern dürfen?
- Sollen abgesagte Flüge im Gruppenfeed sichtbar sein?
- Soll es eine Gruppen-Startseite mit Statistik geben?
- Sollen Rollenhistorien / Mitgliedschaftsänderungen protokolliert werden?
- Soll es Gruppen-Benachrichtigungen geben (neue Flüge, neue Mitglieder)?
- Können Nutzer mehrere Gruppen gründen, oder maximal eine?

---

## 15. Fazit

Diese Regeln bilden die **verbindliche fachliche und technische Grundlage** für die Gruppenfunktion von FlightMeet.

**Wichtigste Punkte zusammengefasst:**
- **Ein Owner pro Gruppe**, unversetzbar, nur durch Transfer
- **Mehrere Admins**, aber nur Admin darf Member -> Admin befördern
- **Offene Gruppen**: direkter Beitritt
- **Private Gruppen**: Anfrage erforderlich, Admin/Owner bestätigt
- **Flug-Sichtbarkeit**: an aktuelle Mitgliedschaft des Erstellers gekoppelt
- **Standardmäßig geplante Flüge**, historische auf Anfrage
- **Alle Prüfungen serverseitig**, keine Logik im Frontend
- **UI orientiert sich an bestehendem FlightMeet-Design**

Die Umsetzung soll so erfolgen, dass **Rechte, Sichtbarkeit und Beitrittslogik ausschließlich serverseitig geprüft werden**.