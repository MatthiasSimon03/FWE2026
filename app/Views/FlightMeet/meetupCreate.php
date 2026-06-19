<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <div class="container">
        <div class="fm-form-card">
            <h1>Neues Flugtreffen erstellen</h1>
            <p class="lead">Fülle das Formular aus, um eine neue Aktivität für die Community zu planen.</p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('flightmeet/meetups/create') ?>" class="fm-form-grid">
                <?= csrf_field() ?>

                <!-- Titel -->
                <label class="fm-field">
                    <span class="fm-field__label">Titel des Treffens</span>
                    <input class="fm-field__input" type="text" name="title" value="<?= esc(old('title')) ?>" placeholder="z. B. Genussfliegen an der Hochries" required>
                </label>

                <!-- Startplatz & Region (Zweispaltig) -->
                <div class="fm-form-grid fm-form-grid--two-cols">
                    <label class="fm-field">
                        <span class="fm-field__label">Flugspot (Startplatz)</span>
                        <input class="fm-field__input" type="text" name="location" value="<?= esc(old('location')) ?>" placeholder="z. B. Hochries Hauptstartplatz" required>
                    </label>
                    <label class="fm-field">
                        <span class="fm-field__label">Region</span>
                        <input class="fm-field__input" type="text" id="region" name="region" value="<?= esc(old('region')) ?>" placeholder="z. B. Chiemgau" required>
                    </label>
                </div>

                <!-- Datum & Uhrzeit (Zweispaltig) -->
                <div class="fm-form-grid fm-form-grid--two-cols">
                    <label class="fm-field">
                        <span class="fm-field__label">Datum des Treffens</span>
                        <input type="text" id="meet_date" name="meet_date" class="fm-field__input" placeholder="Datum auswählen..." required>
                    </label>
                    <label class="fm-field">
                        <span class="fm-field__label">Uhrzeit</span>
                        <input type="text" id="meet_time" name="meet_time" class="fm-field__input" placeholder="Uhrzeit auswählen..." required>
                    </label>
                </div>

                <!-- Erfahrungslevel & Max. Teilnehmer (Zweispaltig) -->
                <div class="fm-form-grid fm-form-grid--two-cols">
                    <label class="fm-field">
                        <span class="fm-field__label">Erfahrungslevel</span>
                        <select class="fm-field__input" name="experience_level" required>
                            <option value="" disabled <?= empty(old('experience_level')) ? 'selected' : '' ?>>Bitte wählen...</option>
                            <option value="Einsteiger" <?= old('experience_level') === 'Einsteiger' ? 'selected' : '' ?>>Einsteiger</option>
                            <option value="Fortgeschritten" <?= old('experience_level') === 'Fortgeschritten' ? 'selected' : '' ?>>Fortgeschritten</option>
                            <option value="Profi" <?= old('experience_level') === 'Profi' ? 'selected' : '' ?>>Profi</option>
                        </select>
                    </label>
                    <label class="fm-field">
                        <span class="fm-field__label">Maximale Teilnehmer</span>
                        <input class="fm-field__input" type="number" name="max_participants" min="1" value="<?= esc(old('max_participants')) ?>" placeholder="z. B. 8" required>
                    </label>
                </div>

                <!-- Interaktive Karte zur Ortsauswahl -->
                <div class="fm-field">
                    <span class="fm-field__label">Startplatz auf der Karte markieren (Klicke auf die Karte)</span>
                    <div id="map-picker"></div>
                    <p id="coords-indicator">❌ Noch kein genauer Startplatz auf der Karte markiert.</p>
                </div>

                <!-- Versteckte Felder für die Datenbank-Koordinaten -->
                <input type="hidden" id="latitude" name="latitude" value="<?= esc(old('latitude')) ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?= esc(old('longitude')) ?>">

                <div style="margin-top: 10px; margin-bottom: 2px;">
                    <label class="fm-checkbox" style="padding: 8px 12px; border-radius: 8px; border-color: var(--color-border-field);">
                        <input type="checkbox" name="creator_is_private" value="1" <?= old('creator_is_private') === '1' ? 'checked' : '' ?>>
                        <span> Als Ersteller anonym bleiben</span>
                    </label>
                </div>

                <!-- Beschreibung (Volle Breite) -->
                <label class="fm-field" style="margin-top: 8px;">
                    <span class="fm-field__label">Beschreibung des Treffens</span>
                    <textarea class="fm-field__input" name="description" rows="5" placeholder="Beschreibe den Ablauf des Treffens..." required><?= esc(old('description')) ?></textarea>
                </label>

                <!-- Aktionen (Buttons) -->
                <div class="actions">
                    <button type="submit" class="btn" style="border: none; cursor: pointer;">Treffen erstellen</button>
                    <a href="<?= base_url('flightmeet/meetups') ?>" class="btn btn-secondary">Abbrechen</a>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>