<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rallyen verwalten</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #333; color: white; padding: 15px 30px; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        h1 { margin-bottom: 20px; }
        .button-group { margin-bottom: 20px; }
        .button-group a { display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .button-group a:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th { background: #007bff; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f9f9f9; }
        .actions a { color: #007bff; text-decoration: none; margin-right: 10px; }
        .actions a:hover { text-decoration: underline; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Rallyen verwalten</h1>
    </div>

    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <div class="button-group">
            <a href="<?= site_url('admin/rallies/create') ?>">+ Neue Rallye</a>
            <a href="<?= site_url('admin') ?>">← Zurück</a>
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
                            <a href="<?= site_url('admin/rallies/' . $rally['id'] . '/edit') ?>">Bearbeiten</a>
                            <a href="<?= site_url('admin/stations/' . $rally['id']) ?>">Stationen</a>
                            <a href="<?= site_url('admin/rallies/' . $rally['id'] . '/delete') ?>" onclick="return confirm('Wirklich löschen?');">Löschen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
