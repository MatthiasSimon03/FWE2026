<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmeldung - FlightMeet</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>
<body class="auth-wrapper">
<div class="auth-card">
    <h1>Anmeldung</h1>

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

    <form method="post" action="<?= base_url('flightmeet/auth/login') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">E-Mail</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Passwort</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group fm-checkbox-wrapper" style="margin-top: 12px;">
            <label class="fm-checkbox" style="border: none; background: transparent; padding: 0;">
                <input type="checkbox" name="remember_me" value="1">
                <span>Eingeloggt bleiben</span>
            </label>
        </div>

        <button type="submit">Anmelden</button>
    </form>

    <div class="link-group">
        <p>Noch nicht registriert? <a href="<?= base_url('flightmeet/auth/register') ?>">Hier registrieren</a></p>
    </div>
</div>
</body>
</html>

