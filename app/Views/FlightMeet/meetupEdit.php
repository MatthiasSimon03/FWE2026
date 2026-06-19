<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <div class="container">
        <div class="fm-form-card">
            <h1>Flugtreffen bearbeiten</h1>
            <p class="lead">Passe die Daten deines Flugtreffens an, um die Community auf dem Laufenden zu halten.</p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('flightmeet/meetups/edit/' . $meetup['id']) ?>" class="fm-form-grid">
                <?= csrf_field() ?>

                <!-- Titel -->
                <label class="fm-field">
                    <span class="fm-field__label">Titel des Treffens</span>
                    <input class="fm-field__input" type="text" name="title" value="<?= esc(old('title', $meetup['title'])) ?>" required>
                </label>

                <!-- Startplatz & Region (Zweispaltig) -->
                <div class="fm-form-grid fm-form-grid--two-cols">
                    <label class="fm-field">
                        <span class="fm-field__label">Flugspot (Startplatz)</span>
                        <input class="fm-field__input" type="text" name="location" value="<?= esc(old('location', $meetup['location'])) ?>" required>
                    </label>
                    <label class="fm-field">
                        <span class="fm-field__label">Region</span>
                        <input class="fm-field__input" type="text" id="region" name="region" value="<?= esc(old('region', $meetup['region'])) ?>" required>
                    </label>
                </div>

                <!-- Datum & Uhrzeit (Zweispaltig) -->
                <div class="fm-form-grid fm-form-grid--two-cols">
                    <label class="fm-field">
                        <span class="fm-field__label">Datum</span>
                        <input class="fm-field__input" id="meet_date" type="text" name="meet_date" value="<?= esc(old('meet_date', $meetup['meet_date'])) ?>" required>
                    </label>
                    <label class="fm-field">
                        <span class="fm-field__label">Uhrzeit</span>
                        <input class="fm-field__input" id="meet_time" type="text" name="meet_time" value="<?= esc(old('meet_time', date('H:i', strtotime($meetup['meet_time'])))) ?>" required>
                    </label>
                </div>

                <!-- Erfahrungslevel & Max. Teilnehmer (Zweispaltig) -->
                <div class="fm-form-grid fm-form-grid--two-cols">
                    <label class="fm-field">
                        <span class="fm-field__label">Erfahrungslevel</span>
                        <select class="fm-field__input" name="experience_level" required>
                            <option value="Einsteiger" <?= old('experience_level', $meetup['experience_level']) === 'Einsteiger' ? 'selected' : '' ?>>Einsteiger</option>
                            <option value="Fortgeschritten" <?= old('experience_level', $meetup['experience_level']) === 'Fortgeschritten' ? 'selected' : '' ?>>Fortgeschritten</option>
                            <option value="Profi" <?= old('experience_level', $meetup['experience_level']) === 'Profi' ? 'selected' : '' ?>>Profi</option>
                        </select>
                    </label>
                    <label class="fm-field">
                        <span class="fm-field__label">Maximale Teilnehmer</span>
                        <input class="fm-field__input" type="number" name="max_participants" min="1" value="<?= esc(old('max_participants', $meetup['max_participants'])) ?>" required>
                    </label>
                </div>

                <!-- Status des Treffens (Zweispaltig, um das Grid-Verhalten symmetrisch zu halten) -->
                <div class="fm-form-grid fm-form-grid--two-cols">
                    <label class="fm-field">
                        <span class="fm-field__label">Status des Treffens</span>
                        <select class="fm-field__input" name="status" required>
                            <option value="geplant" <?= old('status', $meetup['status']) === 'geplant' ? 'selected' : '' ?>>Geplant</option>
                            <option value="ausgebucht" <?= old('status', $meetup['status']) === 'ausgebucht' ? 'selected' : '' ?>>Ausgebucht</option>
                            <option value="abgesagt" <?= old('status', $meetup['status']) === 'abgesagt' ? 'selected' : '' ?>>Abgesagt</option>
                            <option value="abgeschlossen" <?= old('status', $meetup['status']) === 'abgeschlossen' ? 'selected' : '' ?>>Abgeschlossen</option>
                        </select>
                    </label>
                </div>

                <!-- Interaktive Karte zur Ortsauswahl (mit Icon-Pfad-Datenattribut) -->
                <div class="fm-field">
                    <span class="fm-field__label">Startplatz auf der Karte anpassen (Klicke auf die Karte)</span>
                    <div id="map-picker" data-icon-url="<?= base_url('assets/icons/paraglider.png') ?>"></div>
                    <p id="coords-indicator">📍 Startplatz auf der Karte markiert.</p>
                </div>

                <!-- Versteckte Felder für die Datenbank-Koordinaten -->
                <input type="hidden" id="latitude" name="latitude" value="<?= esc(old('latitude', $meetup['latitude'])) ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?= esc(old('longitude', $meetup['longitude'])) ?>">

                <div style="margin-top: 10px; margin-bottom: 2px;">
                    <label class="fm-checkbox" style="padding: 8px 12px; border-radius: 8px; border-color: var(--color-border-field);">
                        <input type="checkbox" name="creator_is_private" value="1" <?= old('creator_is_private', $meetup['creator_is_private']) == '1' ? 'checked' : '' ?>>
                        <span> Als Ersteller anonym bleiben</span>
                    </label>
                </div>

                <!-- Beschreibung (Volle Breite) -->
                <label class="fm-field" style="margin-top: 8px;">
                    <span class="fm-field__label">Beschreibung des Treffens</span>
                    <textarea class="fm-field__input" name="description" rows="5" required><?= esc(old('description', $meetup['description'])) ?></textarea>
                </label>

                <!-- Aktionen (Buttons) -->
                <div class="actions">
                    <button type="submit" class="btn" style="border: none; cursor: pointer;">Änderungen speichern</button>
                    <a href="<?= base_url('flightmeet/meetups/' . $meetup['id']) ?>" class="btn btn-secondary">Abbrechen</a>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>