<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Station bearbeiten</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar { background: #333; color: white; padding: 15px 30px; }
        .container { max-width: 600px; margin: 30px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        input:focus, textarea:focus { outline: none; border-color: #007bff; box-shadow: 0 0 5px rgba(0,123,255,0.3); }
        button { padding: 12px 20px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; }
        button:hover { background: #0056b3; }
        .button-group { display: flex; gap: 10px; }
        .button-group a { display: inline-block; padding: 12px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; }
        .button-group a:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Station bearbeiten</h1>
    </div>
    
    <div class="container">
        <form method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="title">Titel</label>
                <input type="text" id="title" name="title" value="<?= esc($station['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Beschreibung</label>
                <textarea id="description" name="description" rows="4"><?= esc($station['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="latitude">Breitengrad (optional)</label>
                <input type="number" id="latitude" name="latitude" step="0.0000001" value="<?= $station['latitude'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label for="longitude">Längengrad (optional)</label>
                <input type="number" id="longitude" name="longitude" step="0.0000001" value="<?= $station['longitude'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label for="order_index">Reihenfolge</label>
                <input type="number" id="order_index" name="order_index" value="<?= $station['order_index'] ?>">
            </div>
            
            <div class="button-group">
                <button type="submit">Speichern</button>
                <a href="<?= site_url('admin/stations/' . $station['rally_id']) ?>">Abbrechen</a>
            </div>
        </form>
    </div>
</body>
</html>
