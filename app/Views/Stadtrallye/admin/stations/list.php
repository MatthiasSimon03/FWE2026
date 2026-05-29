<?php
if (!isset($rally)) {
    $rally = ['title' => ''];
}
if (!isset($rallyId)) {
    $rallyId = 0;
}
if (!isset($stations)) {
    $stations = [];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stationen verwalten</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <style>
        .rally-title { color: #666; margin-bottom: 20px; }
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
        <h1>Stationen verwalten</h1>
    </div>

    <div class="container">
        <div class="rally-title">
            Rallye: <strong><?= esc($rally['title']) ?></strong>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <div class="button-group">
            <a href="<?= site_url('stadtrallye/admin/stations/' . $rallyId . '/create') ?>">+ Neue Station</a>
            <a href="<?= site_url('stadtrallye/admin/rallies') ?>">← Zurück</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Beschreibung</th>
                    <th>Reihenfolge</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stations as $station): ?>
                    <tr>
                        <td><?= esc($station['title']) ?></td>
                        <td><?= esc(substr($station['description'] ?? '', 0, 50)) ?></td>
                        <td><?= esc((string)($station['order_index'] ?? 0)) ?></td>
                        <td class="actions">
                            <a href="<?= site_url('stadtrallye/admin/stations/edit/' . $station['id']) ?>">Bearbeiten</a>
                            <a href="<?= site_url('stadtrallye/admin/tasks/' . $station['id']) ?>">Aufgaben verwalten</a>
                            <form method="post" action="<?= site_url('stadtrallye/admin/stations/delete/' . $station['id']) ?>" style="display:inline" onsubmit="return confirm('Wirklich löschen?');">
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

