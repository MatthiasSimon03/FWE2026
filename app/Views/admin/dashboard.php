<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }

        /* Wie in den normalen Nutzerseiten */
        .navbar {
            background: #333;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 {
            margin: 0;
            font-size: 32px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        .navbar a:hover {
            text-decoration: underline;
        }

        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        h1 { margin-bottom: 30px; }
        .admin-menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .menu-item { background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .menu-item a { display: inline-block; margin-top: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .menu-item a:hover { background: #0056b3; }
    </style>
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
