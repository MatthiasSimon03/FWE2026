<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Übersicht - Fortgeschrittene Webentwicklung</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>
<body>
<div class="navbar">
    <h1>Fortgeschrittene Webentwicklung</h1>
</div>

<div class="container">
    <h1>Willkommen zur Vorlesung Webentwicklung</h1>

    <div class="overview">
        <p>Übersicht über die Projekte in der Vorlesung</p>
    </div>

    <div class="admin-menu">
        <div class="menu-item">
            <h3>🎯 Stadtrally</h3>
            <p>Hier geht es zur Stadtrally, die mit KI entwickelt wurde.</p>
            <a href="<?= base_url('stadtrallye/auth/login') ?>">Zum Login der Stadtrally</a>
        </div>

        <div class="menu-item">
            <h3>🏆 Tic-Tac-Toe</h3>
            <p>Hier geht es zum entwickelten Spiel Tic-Tac-Toe</p>
            <a href="<?= base_url('tictactoe.html') ?>">Zu Tic Tac Toe</a>
        </div>

        <div class="menu-item">
            <h3>🏆 FlightMeet</h3>
            <p>Hier geht es zur Community-Plattform FlightMeet</p>
            <a href="<?= base_url('flightmeet/auth/login') ?>">zu FlightMeet</a>
        </div>
    </div>
</div>
</body>
</html>