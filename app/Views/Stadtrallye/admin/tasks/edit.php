<?php
if (!isset($task)) {
    $task = ['text' => '', 'answer_type' => 'text', 'answer' => '', 'points' => 1];
}
$stationId = $stationId ?? 0;
$rallyId = $rallyId ?? 0;
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aufgabe bearbeiten</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <style>
        .container { max-width: 600px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .button-group { display: flex; gap: 10px; }
        .button-group a { display: inline-block; padding: 12px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; }
        .button-group a:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Aufgabe bearbeiten</h1>
    </div>

    <div class="container">
        <form method="post" action="<?= site_url('stadtrallye/admin/tasks/edit/' . $task['id']) ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="text">Aufgabentext</label>
                <textarea id="text" name="text" rows="4" required><?= esc($task['text']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="answer_type">Antworttyp</label>
                <select id="answer_type" name="answer_type" required>
                    <option value="text" <?= $task['answer_type'] === 'text' ? 'selected' : '' ?>>Einfacher Text</option>
                    <option value="regex" <?= $task['answer_type'] === 'regex' ? 'selected' : '' ?>>Regular Expression</option>
                    <option value="multiple_choice" <?= $task['answer_type'] === 'multiple_choice' ? 'selected' : '' ?>>Multiple Choice</option>
                </select>
            </div>

            <div class="form-group">
                <label for="answer">Antwort / Pattern</label>
                <textarea id="answer" name="answer" rows="3" required><?= esc($task['answer']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="points">Punkte</label>
                <input type="number" id="points" name="points" value="<?= esc((string)$task['points']) ?>" min="1" required>
            </div>

            <div class="button-group">
                <button type="submit">Speichern</button>
                <a href="<?= site_url('stadtrallye/admin/tasks/' . $stationId) ?>">Abbrechen</a>
            </div>
        </form>
    </div>
</body>
</html>

