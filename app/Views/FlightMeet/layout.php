<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'FlightMeet') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/flightmeet.css') ?>">
    <link rel="icon" href="<?= base_url('assets/icons/favicon.ico') ?>">
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

