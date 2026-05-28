<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rallys - Stadtrallye</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>
<body>
    <div class="navbar">
        <h1>Stadtrallye</h1>
        <div>
            <?php if (session()->get('user_id')): ?>
                <a href="<?= site_url('overview') ?>">Home</a>
                <span><?= esc(session()->get('name')) ?></span>
                <?php if (session()->get('role') === 'admin'): ?>
                    <a href="<?= site_url('admin') ?>" style="color: #ffcc00; font-weight: bold;">Admin</a>
                <?php endif; ?>
                <a href="<?= site_url('leaderboard') ?>">Leaderboard</a>
                <a href="<?= site_url('auth/logout') ?>">Abmelden</a>
            <?php else: ?>
                <a href="<?= site_url('overview') ?>">Home</a>
                <a href="<?= site_url('auth/login') ?>">Anmelden</a>
                <a href="<?= site_url('auth/register') ?>">Registrieren</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h1>Verfügbare Rallys</h1>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($rallies)): ?>
            <div class="rally-grid">
                <?php foreach ($rallies as $rally): ?>
                    <div class="rally-card">
                        <h2><?= esc($rally['title']) ?></h2>
                        <p><?= esc(substr($rally['description'], 0, 150)) ?>...</p>
                        <?php if ($rally['start_time']): ?>
                            <p><strong>Start:</strong> <?= date('d.m.Y H:i', strtotime($rally['start_time'])) ?></p>
                        <?php endif; ?>
                        <a href="<?= site_url('rally/' . $rally['id']) ?>">Mehr erfahren</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-rallies">
                <p>Keine aktiven Rallys verfügbar.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
