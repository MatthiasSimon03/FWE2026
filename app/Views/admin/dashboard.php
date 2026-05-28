<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>
<body>
    <div class="navbar">
        <h1>Admin Dashboard</h1>
        <div>
            <?php if (session()->get('user_id')): ?>
                <span><?= esc(session()->get('name')) ?></span>
                <a href="<?= site_url('rally') ?>">Rallys</a>
                <a href="<?= site_url('auth/logout') ?>">Abmelden</a>
            <?php else: ?>
                <a href="<?= site_url('auth/login') ?>">Anmelden</a>
                <a href="<?= site_url('auth/register') ?>">Registrieren</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h2>Verwaltung</h2>

        <div class="admin-menu">
            <div class="menu-item">
                <h3>Rallys</h3>
                <p>Verwalten Sie Rallys</p>
                <a href="<?= site_url('admin/rallies') ?>">Rallys verwalten</a>
            </div>


        </div>

        <p style="margin-top: 30px; text-align: center;">
            <a href="<?= site_url('auth/logout') ?>" style="color: #007bff; text-decoration: none;">Abmelden</a>
        </p>
    </div>
</body>
</html>
