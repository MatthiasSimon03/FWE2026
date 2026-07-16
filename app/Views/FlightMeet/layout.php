<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Dynamischer Seitentitel mit Standard-Fallback -->
    <title><?= esc($title ?? 'FlightMeet') ?></title>

    <!-- Schrifttyp: Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon-Bibliothek: Phosphor Icons -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">

    <!-- Datepicker: Flatpickr CSS & Theme -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

    <!-- Haupt-Stylesheet und Favicon -->
    <link rel="stylesheet" href="<?= base_url('assets/css/flightmeet.css') ?>">
    <link rel="icon" href="<?= base_url('assets/icons/favicon.ico') ?>">

    <!-- Interaktive Karten: Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
</head>
<body>
<header>
    <!-- Navigation als Partial-View laden; aktive Menüklasse übergeben -->
    <?= view('FlightMeet/partials/nav', ['active' => $active ?? 'home']) ?>
</header>
<main class="container">
    <!-- Platzhalter für die vererbten Inhaltsbereiche der Views -->
    <?= $this->renderSection('content') ?>
</main>

<footer class="footer">
    <p>&copy; 2026 FlightMeet Team 14</p>
</footer>

<!-- App-spezifische JavaScript-Bibliothek -->
<script src="<?= base_url('assets/js/flightmeet.js') ?>"></script>

<!-- Datepicker: Flatpickr JS & deutsche Lokalisierung -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/de.js"></script>

</body>
</html>