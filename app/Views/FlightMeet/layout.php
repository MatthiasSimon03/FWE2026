<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'FlightMeet') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/flightmeet.css') ?>">
</head>
<body>
<header>
    <?= view('FlightMeet/partials/nav', ['active' => $active ?? 'home']) ?>
</header>

<main class="container">
    <?= $this->renderSection('content') ?>
</main>

<script src="<?= base_url('assets/js/flightmeet.js') ?>"></script>
</body>
</html>

