<?php $station = $station ?? ['title' => '', 'rally_id' => 0]; $tasks = $tasks ?? []; $stationId = $stationId ?? 0; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aufgaben verwalten</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <style>
        .station-title { color: #666; margin-bottom: 20px; }
        .button-group { margin-bottom: 20px; }
        .button-group a { display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .button-group a.secondary { background: #6c757d; }
        .button-group a:hover { opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th { background: #007bff; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: top; }
        tr:hover { background: #f9f9f9; }
        .actions a { color: #007bff; text-decoration: none; margin-right: 10px; }
        .actions a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Aufgaben verwalten</h1>
    </div>

    <div class="container">
        <div class="station-title">
            Station: <strong><?= esc($station['title']) ?></strong>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <div class="button-group">
            <a href="<?= site_url('admin/tasks/create/' . $stationId) ?>">+ Neue Aufgabe</a>
            <a class="secondary" href="<?= site_url('admin/stations/' . $station['rally_id']) ?>">← Zurück</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Text</th>
                    <th>Antworttyp</th>
                    <th>Punkte</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tasks)): ?>
                    <tr>
                        <td colspan="4">Noch keine Aufgaben vorhanden.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?= esc(substr($task['text'] ?? '', 0, 120)) ?></td>
                            <td><?= esc($task['answer_type'] ?? '') ?></td>
                            <td><?= esc($task['points'] ?? 0) ?></td>
                            <td class="actions">
                                <a href="<?= site_url('admin/tasks/edit/' . $task['id']) ?>">Bearbeiten</a>
                                <a href="<?= site_url('admin/tasks/delete/' . $task['id']) ?>" onclick="return confirm('Wirklich löschen?');">Löschen</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

