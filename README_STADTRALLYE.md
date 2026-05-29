# Stadtrallye Web-Anwendung

Eine web-basierte Anwendung für die Verwaltung und Durchführung von Stadtrallyen mit CodeIgniter 4, PHP und MySQL.

## 🚀 Schnellstart

### 1. Datenbank-Schema einrichten
- Öffne phpMyAdmin auf deinem Uni-Webspace
- Wähle deine `rallye_db` (oder eine andere Datenbank)
- Gehe zum Reiter **Importieren**
- Lade die Datei `TESTDATEN.sql` hoch und klicke **OK**

Alternativ:
- Importiere zuerst das leere Schema (Code aus der Planung)
- Führe dann `TESTDATEN.sql` aus

### 2. CodeIgniter konfigurieren
- Öffne `.env` im Projekt-Root
- Passe die DB-Einstellungen an:
```
database.default.hostname = <dein-uni-server>
database.default.database = <deine-db-name>
database.default.username = <dein-uni-user>
database.default.password = <dein-passwort>
database.default.DBDriver = MySQLi
```

### 3. Anwendung starten
- Öffne im Browser: `http://localhost/webentwicklung/public` (lokal)
- Oder: `https://dein-uni-webspace.de/webentwicklung/public`

## 🔑 Standard-Zugangsdaten

**Admin-Account:**
- E-Mail: `admin@example.com`
- Passwort: `password`

## 📋 Funktionen

### Für Teilnehmende
- ✅ Registrierung & Anmeldung
- ✅ Rallye-Übersicht
- ✅ Stationen & Aufgaben anschauen
- ✅ Antworten abgeben
- ✅ Punkte sammeln
- ✅ Leaderboard ansehen

### Für Admin
- ✅ Rallyen erstellen/bearbeiten/löschen
- ✅ Stationen verwalten
- ✅ Aufgaben hinzufügen/ändern
- ✅ Datenbank über phpMyAdmin verwenden

## 📂 Projektstruktur

```
app/
├── Controllers/
│   ├── Home.php                    # Startseite (Redirect zu Rallyen)
│   ├── Auth.php                    # Registrierung, Login, Logout
│   ├── RallyController.php         # Rallye-Übersicht & Details
│   ├── StationController.php       # Station & Aufgabe anzeigen
│   ├── LeaderboardController.php   # Leaderboard
│   └── Admin/
│       ├── Dashboard.php           # Admin-Dashboard
│       ├── Rallies.php             # Rallye-Verwaltung
│       ├── Stations.php            # Station-Verwaltung
│       └── Tasks.php               # Aufgaben-Verwaltung
├── Models/
│   ├── UserModel.php               # Benutzerverwaltung
│   ├── RallyModel.php              # Rallye-Daten
│   ├── StationModel.php            # Station-Daten
│   ├── TaskModel.php               # Aufgaben & Auswertung
│   └── SubmissionModel.php         # Antwort-Speicherung & Leaderboard
└── Views/
    ├── auth/register.php           # Registrierungsform
    ├── auth/login.php              # Anmeldeformular
    ├── rally/list.php              # Rallye-Übersicht
    ├── rally/show.php              # Rallye-Details mit Stationen
    ├── station/show.php            # Station mit Aufgaben
    ├── leaderboard/index.php       # Leaderboard-Tabelle
    └── admin/...                   # Admin-Interface
```

### Separater Namespace für die Stadtrallye

Die Stadtrallye-App ist nun zusätzlich unter dem Namespace `App\Controllers\Stadtrallye` strukturiert und über den URL-Prefix `/stadtrallye` erreichbar.

Wichtige Einstiegspunkte:
- `stadtrallye/rally`
- `stadtrallye/station/{id}`
- `stadtrallye/leaderboard`
- `stadtrallye/auth/login`
- `stadtrallye/auth/register`
- `stadtrallye/admin`

Neue Klassen liegen unter:
- `app/Controllers/Stadtrallye/`
- `app/Controllers/Stadtrallye/Admin/`
- `app/Models/Stadtrallye/`

Die bestehende Stadtrallye-Logik bleibt damit parallel zur bisherigen App verfügbar und kann später schrittweise vollständig in diesen Namespace migriert werden.

## 🎯 Nächste Schritte zum Erweitern

### 1. Mehr Rallyen & Stationen hinzufügen
- Admin-Zugang öffnen: `http://.../webentwicklung/public/admin`
- "Rallyen verwalten" → "+ Neue Rallye"
- Stationen hinzufügen & Aufgaben erstellen

### 2. Aufgabentypen nutzen
Im Admin-Bereich "Aufgabe erstellen":
- **text**: Exakte Textübereinstimmung (z.B. "Berlin")
- **regex**: Pattern-Matching (z.B. `/^[0-9]{4}$/` für 4-stellige Zahlen)
- **multiple_choice**: Mehrere Auswahlmöglichkeiten (zukünftig erweitert)

### 3. Benutzer manuell hinzufügen
In phpMyAdmin in `users` Tabelle:
```sql
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`)
VALUES ('John Doe', 'john@example.com', '$2y$10$...', 'user');
```
Passwort-Hash mit PHP generieren:
```powershell
php -r "echo password_hash('dein_passwort', PASSWORD_DEFAULT).PHP_EOL;"
```

### 4. Koordinaten für Karten hinzufügen
Beim Erstellen/Bearbeiten einer Station:
- **Breitengrad** & **Läntengrad** eintragen (z.B. 51.5074, -0.1278)
- Diese können später für eine interaktive Karte (Leaflet/Google Maps) genutzt werden

## 🔧 Troubleshooting

### Problem: "Tabelle nicht gefunden"
- ✅ Prüfe, ob `TESTDATEN.sql` oder das Schema in phpMyAdmin importiert wurde
- ✅ Starte den Browser neu (Cache leeren)

### Problem: "Falsche DB-Zugangsdaten"
- ✅ Prüfe `.env`: Hostname, Username, Passwort korrekt?
- ✅ Kontrolliere in phpMyAdmin, ob DB & User existieren

### Problem: "500 Internal Server Error"
- ✅ Gucke in `writable/logs/` nach Fehlermeldungen
- ✅ Stelle sicher, dass der `writable/` Ordner Schreibrechte hat

### Problem: "Admin-Bereich lässt sich nicht öffnen"
- ✅ Bin ich als Admin eingeloggt? (Rolle = 'admin')
- ✅ DB-Spalte `role` beim Admin auf 'admin' gesetzt?

## 📝 Notizen für die Abgabe

1. **Konfigurationsdatei** nicht committen: `.env` enthält Passwörter!
2. **writable/-Ordner** muss Schreibrechte haben
3. **DB-Backups** vor größeren Änderungen machen (phpMyAdmin → Exportieren)
4. **Session-Sicherheit**: Default-Konfiguration ist okay, aber in Produktion prüfen
5. **CSRF-Schutz** ist aktiv (CodeIgniter Default)

## 🎓 Anforderungen nach Aufgabenstellung

- ✅ Registrierung von Teilnehmenden
- ✅ Anzeige der Rallye-Stationen und Aufgaben
- ✅ Eingabe von Antworten durch die Teilnehmenden
- ✅ Auswertung der Antworten und Vergabe von Punkten
- ✅ Anzeige eines Punktestands (Leaderboard)
- ✅ Verwaltung der Aufgaben, Stationen und Teilnehmer (Admin)

## 📚 Weitere Hilfe

Fragen zu CodeIgniter 4? Siehe: https://codeigniter.com/user_guide/

---

**Version:** 1.0  
**Letzte Änderung:** 2026-05-22  
**Autor:** Stadtrallye-Team

