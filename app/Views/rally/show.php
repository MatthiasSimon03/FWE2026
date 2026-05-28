<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($rally['title']) ?> - Stadtrallye</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <style>
        .station-list { list-style: none; }
        .station-item { background: white; padding: 15px; margin-bottom: 10px; border-left: 4px solid #007bff; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .station-item a { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .station-item a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Stadtrallye</h1>
        <div>
            <?php if (session()->get('user_id')): ?>
                <span><?= esc(session()->get('name')) ?></span>
                <a href="<?= site_url('leaderboard') ?>">Leaderboard</a>
                <a href="<?= site_url('auth/logout') ?>">Abmelden</a>
            <?php else: ?>
                <a href="<?= site_url('auth/login') ?>">Anmelden</a>
                <a href="<?= site_url('auth/register') ?>">Registrieren</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="back-link">
            <a href="<?= site_url('rally') ?>">← Zurück zu Rallys</a>
        </div>

        <h1><?= esc($rally['title']) ?></h1>
        <div class="description">
            <?= esc($rally['description']) ?>
        </div>

        <h2>Stationen</h2>
        <ul class="station-list">
            <?php foreach ($rally['stations'] as $station): ?>
                <li class="station-item">
                    <div>
                        <strong><?= esc($station['title']) ?></strong>
                        <?php if ($station['description']): ?>
                            <p style="color: #666; font-size: 14px; margin-top: 5px;"><?= esc($station['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <a href="<?= site_url('station/' . $station['id']) ?>">Zur Station</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
