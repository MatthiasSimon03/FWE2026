<?php
    $rallies = $rallies ?? [];
    $leaderboard = $leaderboard ?? [];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Stadtrallye</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <style>
        .rally-selector { margin-bottom: 30px; }
        .rally-selector label { margin-right: 10px; font-weight: bold; }
        .rally-selector select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th { background: #007bff; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }
        .rank { font-weight: bold; color: #007bff; }
        .medal { font-size: 1.2em; }
        .no-data { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Stadtrallye</h1>
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
        <div class="back-link">
            <a href="<?= site_url('rally') ?>">← Zurück zu Rallys</a>
        </div>

        <h1>Leaderboard</h1>

        <?php if (!empty($rallies)): ?>
            <div class="rally-selector">
                <label for="rally_select">Rallye:</label>
                <select id="rally_select" onchange="window.location.href='<?= site_url('leaderboard') ?>/' + this.value">
                    <option value="">-- Wählen Sie eine Rallye --</option>
                    <?php foreach ($rallies as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($r['id'] == ($rallyId ?? null)) ? 'selected' : '' ?>>
                            <?= esc($r['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if (!empty($selectedRally ?? null) && !empty($leaderboard)): ?>
            <h2><?= esc($selectedRally['title']) ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Platzierung</th>
                        <th>Name</th>
                        <th>Punkte</th>
                        <th>Abgaben</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $entry): ?>
                        <tr>
                            <td class="rank">
                                <?php if ($index === 0): ?>
                                    <span class="medal">🥇</span>
                                <?php elseif ($index === 1): ?>
                                    <span class="medal">🥈</span>
                                <?php elseif ($index === 2): ?>
                                    <span class="medal">🥉</span>
                                <?php else: ?>
                                    #<?= $index + 1 ?>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($entry['name']) ?></td>
                            <td><strong><?= $entry['score'] ?? 0 ?></strong></td>
                            <td><?= $entry['submissions_count'] ?? 0 ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <?php if (empty($selectedRally ?? null)): ?>
                    <p>Bitte wählen Sie eine Rallye aus dem Dropdown-Menü.</p>
                <?php else: ?>
                    <p>Für diese Rallye gibt es noch keine Abgaben.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
