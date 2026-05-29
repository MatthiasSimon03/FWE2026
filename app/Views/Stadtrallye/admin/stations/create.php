<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Station erstellen</title>
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
        <h1>Station erstellen</h1>
    </div>

    <div class="container">
        <form method="post" action="<?= site_url('stadtrallye/admin/stations/' . $rallyId . '/create') ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title">Titel</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">Beschreibung</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="latitude">Breitengrad (optional)</label>
                <input type="number" id="latitude" name="latitude" step="0.0000001" placeholder="z.B. 51.5074">
            </div>

            <div class="form-group">
                <label for="longitude">Längengrad (optional)</label>
                <input type="number" id="longitude" name="longitude" step="0.0000001" placeholder="z.B. -0.1278">
            </div>

            <div class="form-group">
                <label for="order_index">Reihenfolge</label>
                <input type="number" id="order_index" name="order_index" value="0">
            </div>

            <div class="button-group">
                <button type="submit">Erstellen</button>
                <a href="<?= site_url('stadtrallye/admin/stations/' . $rallyId) ?>">Abbrechen</a>
            </div>
        </form>
    </div>
</body>
</html>

