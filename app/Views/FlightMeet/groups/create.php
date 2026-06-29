<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <div class="container">
        <div class="fm-form-card">
            <h1>Gruppe gründen</h1>
            <p class="lead">Bilde eine neue Regional- oder Interessengruppe für eure gemeinsame Flugplanung.</p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('flightmeet/groups/create') ?>" class="fm-form-grid">
                <?= csrf_field() ?>

                <!-- Gruppenname -->
                <label class="fm-field">
                    <span class="fm-field__label">Name der Gruppe</span>
                    <input class="fm-field__input" type="text" name="name" value="<?= old('name') ?>" required placeholder="z.B. Moselflieger e.V.">
                </label>

                <!-- Fokusregion & Sichtbarkeit -->
                <div class="fm-form-grid fm-form-grid--two-cols">
                    <label class="fm-field">
                        <span class="fm-field__label">Fokusregion / Basis</span>
                        <input class="fm-field__input" type="text" id="region" name="region" value="<?= old('region') ?>" placeholder="z.B. Mosel, Sauerland" required>
                    </label>
                    <label class="fm-field">
                        <span class="fm-field__label">Sichtbarkeit</span>
                        <select class="fm-field__input" name="visibility" required>
                            <option value="open">Offen (Jeder kann sofort beitreten)</option>
                            <option value="private">Privat (Admins prüfen Beitrittsanfragen)</option>
                        </select>
                    </label>
                </div>

                <!-- Stützpunkt Freitext -->
                <label class="fm-field">
                    <span class="fm-field__label">Basis-Standort (Name)</span>
                    <input class="fm-field__input" type="text" name="base_location" value="<?= old('base_location') ?>" placeholder="z.B. Startplatz Klüsserath (Süd/West)" required>
                </label>

                <!-- Karte zur Stützpunkt-Ortsauswahl -->
                <div class="fm-field">
                    <span class="fm-field__label">Stützpunkt auf der Karte markieren (Optional)</span>
                    <div id="map-picker" data-icon-url="<?= base_url('assets/icons/paraglider.png') ?>"></div>
                    <p id="coords-indicator">📍 Klicke auf die Karte, um den Haupttreffpunkt festzulegen.</p>
                </div>

                <input type="hidden" id="latitude" name="latitude" value="<?= old('latitude') ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?= old('longitude') ?>">

                <!-- Gruppenbeschreibung -->
                <label class="fm-field">
                    <span class="fm-field__label">Gruppenbeschreibung</span>
                    <textarea class="fm-field__input" name="description" rows="4" placeholder="Schreibe ein paar Zeilen über den Zweck eurer Gruppe..." required><?= old('description') ?></textarea>
                </label>

                <!-- Gruppenregeln -->
                <label class="fm-field">
                    <span class="fm-field__label">Gruppenregeln / Richtlinien (Optional)</span>
                    <textarea class="fm-field__input" name="rules" rows="4" placeholder="z.B. Nur für Piloten mit A-Schein; Keine kommerzielle Werbung..."><?= old('rules') ?></textarea>
                </label>

                <div class="actions">
                    <button type="submit" class="btn" style="border: none; cursor: pointer;">Gruppe gründen</button>
                    <a href="<?= base_url('flightmeet/groups') ?>" class="btn btn-secondary">Abbrechen</a>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>