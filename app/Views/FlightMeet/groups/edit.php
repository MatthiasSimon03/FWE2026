<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <div class="container">
        <div class="fm-form-card">
            <h1>Gruppe bearbeiten</h1>
            <p class="lead">Passe die Richtlinien, den Treffpunkt oder die Sichtbarkeit der Gruppe an.</p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('flightmeet/groups/edit/' . $group['id']) ?>" class="fm-form-grid">
                <?= csrf_field() ?>

                <!-- Gruppenname (nicht editierbar zur System-Konsistenz) -->
                <label class="fm-field">
                    <span class="fm-field__label">Name der Gruppe (kann nicht geändert werden)</span>
                    <input class="fm-field__input" type="text" value="<?= esc($group['name']) ?>" disabled style="background-color: var(--color-bg-light);">
                </label>

                <!-- Fokusregion & Sichtbarkeit -->
                <div class="fm-form-grid fm-form-grid--two-cols">
                    <label class="fm-field">
                        <span class="fm-field__label">Fokusregion / Basis</span>
                        <input class="fm-field__input" type="text" id="region" name="region" value="<?= esc(old('region', $group['region'])) ?>" required>
                    </label>
                    <label class="fm-field">
                        <span class="fm-field__label">Sichtbarkeit</span>
                        <select class="fm-field__input" name="visibility" required>
                            <option value="open" <?= old('visibility', $group['visibility']) === 'open' ? 'selected' : '' ?>>Offen (Jeder kann sofort beitreten)</option>
                            <option value="private" <?= old('visibility', $group['visibility']) === 'private' ? 'selected' : '' ?>>Privat (Admins prüfen Beitrittsanfragen)</option>
                        </select>
                    </label>
                </div>

                <!-- Stützpunkt Freitext -->
                <label class="fm-field">
                    <span class="fm-field__label">Basis-Standort (Name)</span>
                    <input class="fm-field__input" type="text" name="base_location" value="<?= esc(old('base_location', $group['base_location'])) ?>" required>
                </label>

                <!-- Karte zur Stützpunkt-Ortsauswahl -->
                <div class="fm-field">
                    <span class="fm-field__label">Stützpunkt auf der Karte anpassen (Klicke auf die Karte)</span>
                    <div id="map-picker" data-icon-url="<?= base_url('assets/icons/paraglider.png') ?>"></div>
                    <p id="coords-indicator">📍 Stützpunkt auf der Karte markiert.</p>
                </div>

                <input type="hidden" id="latitude" name="latitude" value="<?= esc(old('latitude', $group['latitude'])) ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?= esc(old('longitude', $group['longitude'])) ?>">

                <!-- Gruppenbeschreibung -->
                <label class="fm-field">
                    <span class="fm-field__label">Gruppenbeschreibung</span>
                    <textarea class="fm-field__input" name="description" rows="4" required><?= esc(old('description', $group['description'])) ?></textarea>
                </label>

                <!-- Gruppenregeln -->
                <label class="fm-field">
                    <span class="fm-field__label">Gruppenregeln / Richtlinien</span>
                    <textarea class="fm-field__input" name="rules" rows="4"><?= esc(old('rules', $group['rules'])) ?></textarea>
                </label>

                <div class="actions">
                    <button type="submit" class="btn" style="border: none; cursor: pointer;">Änderungen speichern</button>
                    <a href="<?= base_url('flightmeet/groups/detail/' . $group['id']) ?>" class="btn btn-secondary">Abbrechen</a>
                </div>
            </form>
        </div>
    </div>

<?= $this->endSection() ?>