<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung - FlightMeet</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
</head>
<body class="auth-wrapper">
<div class="auth-card">
    <h1>Registrierung</h1>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('flightmeet/auth/register') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= esc(old('name')) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">E-Mail</label>
            <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required>
        </div>

        <div class="form-group">
            <label for="experience_level">Erfahrungslevel</label>
            <select id="experience_level" name="experience_level" required>
                <option value="" disabled <?= empty(old('experience_level')) ? 'selected' : '' ?>>Bitte wählen...</option>
                <option value="Einsteiger" <?= old('experience_level') === 'Einsteiger' ? 'selected' : '' ?>>Einsteiger</option>
                <option value="Fortgeschritten" <?= old('experience_level') === 'Fortgeschritten' ? 'selected' : '' ?>>Fortgeschritten</option>
                <option value="Profi" <?= old('experience_level') === 'Profi' ? 'selected' : '' ?>>Profi</option>
            </select>
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
        <p>Bereits registriert? <a href="<?= base_url('flightmeet/auth/login') ?>">Hier anmelden</a></p>
    </div>
</div>
</body>
</html>

