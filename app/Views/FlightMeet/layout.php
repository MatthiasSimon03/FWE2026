<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'FlightMeet') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/flightmeet.css') ?>">
    <link rel="icon" href="<?= base_url('assets/icons/favicon.ico') ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
</head>
<body>
<header>
    <?= view('FlightMeet/partials/nav', ['active' => $active ?? 'home']) ?>
</header>
<main class="container">
    <?= $this->renderSection('content') ?>
</main>

<footer>
    <?= view('FlightMeet/partials/footer') ?>
</footer>

<script src="<?= base_url('assets/js/flightmeet.js') ?>"></script>

</body>
</html>

