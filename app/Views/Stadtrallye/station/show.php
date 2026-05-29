<?php
$station = $station ?? ['title' => '', 'description' => '', 'tasks' => []];
/** @var array $station */
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Station - Stadtrallye</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>
<body>
<div class="navbar">
    <h1>Stadtrallye</h1>
    <div>
        <?php if (session()->get('user_id')): ?>
            <span><?= esc(session()->get('name')) ?></span>
            <a href="<?= site_url('stadtrallye/leaderboard') ?>">Leaderboard</a>
            <form method="post" action="<?= site_url('stadtrallye/auth/logout') ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;padding:0;">Abmelden</button>
            </form>
        <?php else: ?>
            <a href="<?= site_url('stadtrallye/auth/login') ?>">Anmelden</a>
            <a href="<?= site_url('stadtrallye/auth/register') ?>">Registrieren</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <div class="back-link">
        <a href="<?= site_url('stadtrallye/rally') ?>">← Zurück zu Rallys</a>
    </div>

    <h1><?= esc($station['title']) ?></h1>
    <div class="description">
        <?= esc($station['description']) ?>
    </div>

    <?php if (!empty($station['latitude']) && !empty($station['longitude'])): ?>
        <p style="color: #666; margin-bottom: 20px;">
            <strong>Koordinaten:</strong> <?= esc((string)$station['latitude']) ?>, <?= esc((string)$station['longitude']) ?>
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
            <p><strong>Punkte:</strong> <?= esc((string)($task['points'] ?? 0)) ?></p>

            <?php if (!empty($task['already_solved'])): ?>
                <p class="solved-note">✓ Bereits korrekt gelöst</p>
            <?php else: ?>
                <form method="post" action="<?= site_url('stadtrallye/station/task/' . $task['id'] . '/submit') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="answer_<?= esc((string)$task['id']) ?>">Ihre Antwort:</label>

                        <?php if ($task['answer_type'] === 'multiple_choice' && !empty($task['meta'])): ?>
                            <?php
                            $meta = json_decode($task['meta'], true);
                            $options = $meta['options'] ?? [];
                            ?>
                            <select id="answer_<?= esc((string)$task['id']) ?>" name="answer" required>
                                <option value="">-- Bitte wählen --</option>
                                <?php foreach ($options as $option): ?>
                                    <option value="<?= esc($option) ?>"><?= esc($option) ?></option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($task['answer_type'] === 'photo'): ?>
                            <input type="file" id="answer_<?= esc((string)$task['id']) ?>" name="photo" accept="image/*" required>

                        <?php elseif ($task['answer_type'] === 'number'): ?>
                            <input type="number" step="any" id="answer_<?= esc((string)$task['id']) ?>" name="answer" required placeholder="Zahl eingeben...">

                        <?php else: ?>
                            <input type="text" id="answer_<?= esc((string)$task['id']) ?>" name="answer" required placeholder="Geben Sie Ihre Antwort ein...">
                        <?php endif; ?>
                    </div>

                    <button type="submit">Antwort abgeben</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>

