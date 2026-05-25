<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Station - Stadtrallye</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #333; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .navbar a:hover { text-decoration: underline; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #333; margin-bottom: 10px; }
        .description { color: #666; margin-bottom: 30px; line-height: 1.6; }
        .task-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .task-box h3 { color: #007bff; margin-bottom: 15px; }
        .task-box p { color: #555; line-height: 1.6; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="number"], input[type="file"], select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; background: white; }
        input[type="text"]:focus, input[type="number"]:focus, select:focus { outline: none; border-color: #007bff; box-shadow: 0 0 5px rgba(0,123,255,0.3); }
        button { padding: 12px 20px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background: #0056b3; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .back-link { display: inline-block; margin-bottom: 20px; }
        .back-link a { color: #007bff; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="navbar">
    <h1>Stadtrallye</h1>
    <div>
        <?php if (session()->get('user_id')): ?>
            <span><?= esc(session()->get('name')) ?></span>
            <a href="<?= site_url('leaderboard') ?>">Leaderboard</a>
            <a href="<?= site_url('auth/logout') ?>">Abmelden</a>
        <?php else: ?>
            <a href="<?= site_url('auth/login') ?>">Anmelden</a>
            <a href="<?= site_url('auth/register') ?>">Registrieren</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="back-link">
        <a href="<?= site_url('rally') ?>">← Zurück zu Rallyen</a>
    </div>

    <h1><?= esc($station['title']) ?></h1>
    <div class="description">
        <?= esc($station['description']) ?>
    </div>

    <?php if (!empty($station['latitude']) && !empty($station['longitude'])): ?>
        <p style="color: #666; margin-bottom: 20px;">
            <strong>Koordinaten:</strong> <?= $station['latitude'] ?>, <?= $station['longitude'] ?>
        </p>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php foreach ($station['tasks'] as $task): ?>
        <div class="task-box">
            <h3><?= esc($task['text']) ?></h3>
            <p><strong>Punkte:</strong> <?= $task['points'] ?></p>

            <!-- enctype hinzugefügt, damit Datei-Uploads (Fotos) funktionieren -->
            <form method="post" action="<?= site_url('station/task/' . $task['id'] . '/submit') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="answer_<?= $task['id'] ?>">Ihre Antwort:</label>

                    <?php if ($task['answer_type'] === 'multiple_choice' && !empty($task['meta'])): ?>
                        <!-- Dropdown für Multiple Choice (aus dem JSON-meta Feld geladen) -->
                        <?php
                        $meta = json_decode($task['meta'], true);
                        $options = $meta['options'] ?? [];
                        ?>
                        <select id="answer_<?= $task['id'] ?>" name="answer" required>
                            <option value="">-- Bitte wählen --</option>
                            <?php foreach ($options as $option): ?>
                                <option value="<?= esc($option) ?>"><?= esc($option) ?></option>
                            <?php endforeach; ?>
                        </select>

                    <?php elseif ($task['answer_type'] === 'photo'): ?>
                        <!-- Foto-Upload-Feld -->
                        <input type="file" id="answer_<?= $task['id'] ?>" name="photo" accept="image/*" required>

                    <?php elseif ($task['answer_type'] === 'number'): ?>
                        <!-- Zahlenfeld -->
                        <input type="number" step="any" id="answer_<?= $task['id'] ?>" name="answer" required placeholder="Zahl eingeben...">

                    <?php else: ?>
                        <!-- Standard-Textfeld -->
                        <input type="text" id="answer_<?= $task['id'] ?>" name="answer" required placeholder="Geben Sie Ihre Antwort ein...">
                    <?php endif; ?>
                </div>

                <button type="submit">Antwort abgeben</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>