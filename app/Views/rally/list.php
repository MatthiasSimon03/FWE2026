<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rallyen - Stadtrallye</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #333; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        h1 { margin-bottom: 20px; color: #333; }
        .rally-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .rally-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .rally-card h2 { color: #007bff; margin-bottom: 10px; }
        .rally-card p { color: #666; margin-bottom: 5px; line-height: 1.6; }
        .rally-card a { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .rally-card a:hover { background: #0056b3; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .no-rallies { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Stadtrallye</h1>
        <div>
            <?php if (session()->get('user_id')): ?>
                <span><?= esc(session()->get('name')) ?></span>
                <?php if (session()->get('role') === 'admin'): ?>
                    <a href="<?= site_url('admin') ?>" style="color: #ffcc00; font-weight: bold;">Admin</a>
                <?php endif; ?>
                <a href="<?= site_url('leaderboard') ?>">Leaderboard</a>
                <a href="<?= site_url('auth/logout') ?>">Abmelden</a>
            <?php else: ?>
                <a href="<?= site_url('auth/login') ?>">Anmelden</a>
                <a href="<?= site_url('auth/register') ?>">Registrieren</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h1>Verfügbare Rallyen</h1>

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
                <p>Keine aktiven Rallyen verfügbar.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
