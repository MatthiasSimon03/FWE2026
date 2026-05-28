<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rallye bearbeiten</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    <style>
        .container { max-width: 600px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .checkbox-group { display: flex; align-items: center; }
        .checkbox-group input { width: auto; margin-right: 10px; }
        .button-group { display: flex; gap: 10px; }
        .button-group a { display: inline-block; padding: 12px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; }
        .button-group a:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Rallye bearbeiten</h1>
    </div>
    
    <div class="container">
        <form method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="title">Titel</label>
                <input type="text" id="title" name="title" value="<?= esc($rally['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Beschreibung</label>
                <textarea id="description" name="description" rows="4"><?= esc($rally['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="start_time">Start (optional)</label>
                <input type="datetime-local" id="start_time" name="start_time" value="<?= $rally['start_time'] ? str_replace(' ', 'T', $rally['start_time']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="end_time">Ende (optional)</label>
                <input type="datetime-local" id="end_time" name="end_time" value="<?= $rally['end_time'] ? str_replace(' ', 'T', $rally['end_time']) : '' ?>">
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= $rally['is_active'] ? 'checked' : '' ?>>
                <label for="is_active">Aktiv</label>
            </div>
            
            <div class="button-group">
                <button type="submit">Speichern</button>
                <a href="<?= site_url('admin/rallies') ?>">Abbrechen</a>
            </div>
        </form>
    </div>
</body>
</html>
