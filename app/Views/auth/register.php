<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung - Stadtrallye</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 400px; width: 100%; }
        h1 { margin-bottom: 30px; color: #007bff; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        input:focus { outline: none; border-color: #007bff; box-shadow: 0 0 5px rgba(0,123,255,0.3); }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        button:hover { background: #0056b3; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .link-group { text-align: center; margin-top: 20px; }
        .link-group a { color: #007bff; text-decoration: none; }
        .link-group a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrierung</h1>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= old('name') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-Mail</label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Passwort wiederholen</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            
            <button type="submit">Registrieren</button>
        </form>
        
        <div class="link-group">
            <p>Bereits registriert? <a href="<?= site_url('auth/login') ?>">Hier anmelden</a></p>
        </div>
    </div>
</body>
</html>
