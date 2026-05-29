<?php $stationId = $stationId ?? 0; $rallyId = $rallyId ?? 0; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aufgabe erstellen</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <style>
        .container { max-width: 600px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .button-group { display: flex; gap: 10px; }
        .button-group a { display: inline-block; padding: 12px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; }
        .button-group a:hover { background: #5a6268; }
        .help-text { font-size: 12px; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Aufgabe erstellen</h1>
    </div>

    <div class="container">
        <form method="post" action="<?= site_url('stadtrallye/admin/tasks/create/' . $stationId) ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="text">Aufgabentext</label>
                <textarea id="text" name="text" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="answer_type">Antworttyp</label>
                <select id="answer_type" name="answer_type" required>
                    <option value="text">Einfacher Text</option>
                    <option value="regex">Regular Expression</option>
                    <option value="multiple_choice">Multiple Choice</option>
                </select>
                <div class="help-text">
                    text: Exakte Textübereinstimmung<br>
                    regex: Regex-Pattern-Matching<br>
                    multiple_choice: Mehrere Auswahlmöglichkeiten
                </div>
            </div>

            <div class="form-group">
                <label for="answer">Antwort / Pattern</label>
                <textarea id="answer" name="answer" rows="3" placeholder="z.B. 'Berlin' oder '/^[A-Z][a-z]+$/'" required></textarea>
            </div>

            <div class="form-group">
                <label for="points">Punkte</label>
                <input type="number" id="points" name="points" value="1" min="1" required>
            </div>

            <div class="button-group">
                <button type="submit">Erstellen</button>
                <a href="<?= site_url('stadtrallye/admin/tasks/' . $stationId) ?>">Abbrechen</a>
            </div>
        </form>
    </div>
</body>
</html>

