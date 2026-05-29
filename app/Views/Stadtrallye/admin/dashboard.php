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
                <a href="<?= site_url('stadtrallye/rally') ?>">Rallys</a>
                <form method="post" action="<?= site_url('stadtrallye/auth/logout') ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;padding:0;">Abmelden</button>
                </form>
            <?php else: ?>
                <a href="<?= site_url('stadtrallye/auth/login') ?>">Anmelden</a>
                <a href="<?= site_url('stadtrallye/auth/register') ?>">Registrieren</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h2>Verwaltung</h2>

        <div class="admin-menu">
            <div class="menu-item">
                <h3>Rallys</h3>
                <p>Verwalten Sie Rallys</p>
                <a href="<?= site_url('stadtrallye/admin/rallies') ?>">Rallys verwalten</a>
            </div>
        </div>

        <p style="margin-top: 30px; text-align: center;">
            <form method="post" action="<?= site_url('stadtrallye/auth/logout') ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" style="background:none;border:none;color:#007bff;text-decoration:none;cursor:pointer;padding:0;">Abmelden</button>
            </form>
        </p>
    </div>
</body>
</html>

