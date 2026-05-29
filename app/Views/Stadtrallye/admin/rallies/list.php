<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rallys verwalten</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <style>
        .button-group { margin-bottom: 20px; }
        .button-group a { display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .button-group a:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th { background: #007bff; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }
        .actions a { color: #007bff; text-decoration: none; margin-right: 10px; }
        .actions a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Rallys verwalten</h1>
    </div>

    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <div class="button-group">
            <a href="<?= site_url('stadtrallye/admin/rallies/create') ?>">+ Neue Rallye</a>
            <a href="<?= site_url('stadtrallye/admin') ?>">← Zurück</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Status</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rallies as $rally): ?>
                    <tr>
                        <td><?= esc($rally['title']) ?></td>
                        <td><?= $rally['is_active'] ? '🟢 Aktiv' : '🔴 Inaktiv' ?></td>
                        <td class="actions">
                            <a href="<?= site_url('stadtrallye/admin/rallies/' . $rally['id'] . '/edit') ?>">Bearbeiten</a>
                            <a href="<?= site_url('stadtrallye/admin/stations/' . $rally['id']) ?>">Stationen</a>
                            <form method="post" action="<?= site_url('stadtrallye/admin/rallies/' . $rally['id'] . '/delete') ?>" style="display:inline" onsubmit="return confirm('Wirklich löschen?');">
                                <?= csrf_field() ?>
                                <button type="submit" style="background:none;border:none;color:#007bff;cursor:pointer;padding:0;">Löschen</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

