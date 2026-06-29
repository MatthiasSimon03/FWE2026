<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

    <div class="fm-form-card" style="max-width: 700px; margin-top: 30px;">
        <h1 style="display: flex; align-items: center; justify-content: center; gap: 10px;">
            <i class="ph ph-lock-key" style="color: var(--color-status-ausgebucht);"></i> Private Gruppe
        </h1>
        <p class="lead">Diese Gruppe ist privat. Um Flüge, Koordinaten und die Mitgliederliste einzusehen, stelle eine Beitrittsanfrage an die Admins.</p>

        <div class="card" style="margin: 24px 0; text-align: left; background-color: var(--color-bg-light);">
            <h3 style="margin-top: 0; color: var(--color-text-title);"><?= esc($group['name']) ?></h3>
            <p style="color: var(--color-text-muted-dark); line-height: 1.5;"><?= esc($group['description']) ?></p>
            <p style="font-size: 0.9rem; color: var(--color-text-muted);">📍 Region: <strong><?= esc($group['region'] ?: 'Keine Angabe') ?></strong></p>
        </div>

        <?php if ($has_pending): ?>
            <div class="alert alert-success" style="justify-content: center; font-weight: 600;">
                <i class="ph ph-hourglass-high" style="margin-right: 8px;"></i> Deine Beitrittsanfrage ist ausstehend und wird geprüft.
            </div>
        <?php else: ?>
            <form method="post" action="<?= base_url('flightmeet/groups/request-join/' . $group['id']) ?>">
                <?= csrf_field() ?>
                <label class="fm-field" >
                    <span class="fm-field__label">Kurze Nachricht an den Admin (Optional)</span>
                    <textarea class="fm-field__input" name="message" rows="3" placeholder="Hi! Ich fliege seit 3 Jahren und bin oft in der Region unterwegs..."></textarea>
                </label>
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button type="submit" class="btn" style="flex: 1; border: none; cursor: pointer;">Beitrittsanfrage senden</button>
                    <a href="<?= base_url('flightmeet/groups') ?>" class="btn btn-secondary">Zurück zur Übersicht</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

<?= $this->endSection() ?>