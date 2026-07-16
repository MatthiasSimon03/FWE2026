<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <!-- Feedback-Meldungen bei Erfolgen oder Fehlern (z. B. nach Erstellen einer Gruppe) -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php
// Sichere Fallbacks für die Filtervariablen
$search = $search ?? '';
$my_groups = $my_groups ?? false;
?>

    <h1>Gruppen <img src="<?= base_url('assets/icons/gliderIcon.png') ?>" alt="Icon" class="fm-icon"></h1>
    <p class="lead">Finde bestehende Gruppen oder gründe eine neue Community für eure nächsten Flugtreffen.</p>

    <!-- Such- und Filterformular für Gruppen -->
    <form class="fm-filter" method="get" action="<?= base_url('flightmeet/groups') ?>">
        <div class="fm-filter__row" style="grid-template-columns: 1fr;">
            <label class="fm-field">
                <span class="fm-field__label">Suche nach Gruppen</span>
                <input class="fm-field__input" type="search" name="q" placeholder="Name, Fokusregion oder Beschreibung der Gruppe" value="<?= esc($search) ?>">
            </label>
        </div>
        <div class="fm-filter__actions">
            <button class="btn" type="submit">Suchen</button>
            <a class="btn btn-secondary" href="<?= base_url('flightmeet/groups') ?>">Zurücksetzen</a>

            <!-- Checkbox filtert auf Datenbankebene nach eigenen Gruppenmitgliedschaften -->
            <div class="fm-checkbox-group">
                <label class="fm-checkbox">
                    <input
                            type="checkbox"
                            name="my_groups"
                            value="1"
                            <?= $my_groups ? 'checked' : '' ?>
                            onchange="this.form.submit()"
                    >
                    <span>Nur meine Gruppen</span>
                </label>
            </div>
        </div>
    </form>

    <!-- Kontrollleiste: Zähler und Aktions-Button -->
    <div class="fm-controls-bar">
        <div style="font-size: 0.95rem; color: var(--color-text-muted);">
            <?= count($groups) ?> Gruppen gefunden
        </div>
        <a href="<?= base_url('flightmeet/groups/create') ?>" class="btn-new-meet">
            <i class="ph ph-plus"></i> Neue Gruppe gründen
        </a>
    </div>

    <!-- Auflistung der Gruppen im Kachel-Grid -->
    <section class="fm-section">
        <?php if (empty($groups)): ?>
            <p class="fm-empty">Keine Gruppen für die aktuelle Suche gefunden.</p>
        <?php else: ?>
            <div class="fm-grid">
                <?php foreach ($groups as $group): ?>
                    <div class="fm-card" style="justify-content: space-between;">
                        <div>
                            <header class="fm-card__header">
                                <h3><?= esc($group['name']) ?></h3>
                                <!-- Gruppen-Sichtbarkeit -->
                                <?php if ($group['visibility'] === 'private'): ?>
                                    <span class="fm-status fm-status--ausgebucht">Privat</span>
                                <?php else: ?>
                                    <span class="fm-status fm-status--aktiv">Offen</span>
                                <?php endif; ?>
                            </header>
                            <p class="fm-card__desc" style="margin-top: 10px;"><?= esc($group['description']) ?></p>

                            <!-- Gruppen-Spezifikationen -->
                            <dl class="fm-card__meta" style="margin-top: 15px;">
                                <div>
                                    <dt>Fokusregion</dt>
                                    <dd><?= esc($group['region'] ?: 'Keine Angabe') ?></dd>
                                </div>
                                <div>
                                    <dt>Mitglieder</dt>
                                    <dd><?= esc($group['members_count']) ?> Piloten</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Interaktionsbereich am Kartenfuß -->
                        <div style="margin-top: 15px; border-top: 1px dashed var(--color-border-medium); padding-top: 12px; display: flex; gap: 8px;">
                            <a href="<?= base_url('flightmeet/groups/detail/' . $group['id']) ?>" class="btn" style="padding: 8px 14px; font-size: 0.9rem; flex: 1;">Details</a>

                            <!-- Anzeige des aktuellen Verknüpfungsstatus zum Nutzer -->
                            <?php if ($group['is_member']): ?>
                                <span class="fm-status fm-status--geplant" style="display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem; padding: 0 12px; border-radius: 6px;">
                                <i class="ph ph-check" style="margin-right: 4px;"></i> Mitglied
                            </span>
                            <?php elseif ($group['visibility'] === 'private' && $group['has_pending']): ?>
                                <span class="fm-status fm-status--abgeschlossen" style="display: flex; align-items: center; justify-content: center; font-size: 0.85rem; padding: 0 12px; border-radius: 6px;">
                                Angefragt
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

<?= $this->endSection() ?>