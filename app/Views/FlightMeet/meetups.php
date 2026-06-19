<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<h1>Flugtreffen <img src="<?= base_url('assets/icons/gliderIcon.png') ?>" alt="Icon" class="fm-icon"></h1>
<p class="lead">
    Entdecke anstehende Flugtreffen und verabrede dich mit anderen Interessierten.
</p>

<?php $meetups = $meetups ?? []; ?>
<?php $filters = $filters ?? ['q' => '', 'region' => '', 'level' => '']; ?>
<?php $options = $options ?? ['regions' => [], 'levels' => []]; ?>
<?php $selectedStatus = $filters['status'] ?? []; ?>
<?php $statusOptions = ['geplant', 'ausgebucht', 'abgeschlossen', 'abgesagt'] ?>

<form class="fm-filter" method="get" action="<?= base_url('flightmeet/meetups') ?>">
    <div class="fm-filter__row">
        <label class="fm-field">
            <span class="fm-field__label">Suche</span>
            <input
                class="fm-field__input"
                type="search"
                name="q"
                placeholder="Titel, Flugspot, Region oder Beschreibung"
                value="<?= esc($filters['q']) ?>"
            >
        </label>

        <label class="fm-field">
            <span class="fm-field__label">Region</span>
            <select class="fm-field__input" name="region">
                <option value="">Alle Regionen</option>
                <?php foreach ($options['regions'] as $region): ?>
                    <option value="<?= esc($region) ?>" <?= $filters['region'] === $region ? 'selected' : '' ?>>
                        <?= esc($region) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="fm-field">
            <span class="fm-field__label">Erfahrungslevel</span>
            <select class="fm-field__input" name="level">
                <option value="">Beliebig</option>
                <?php foreach ($options['levels'] as $level): ?>
                    <option value="<?= esc($level) ?>" <?= $filters['level'] === $level ? 'selected' : '' ?>>
                        <?= esc($level) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>

    <div class="fm-filter__actions">
        <button class="btn" type="submit"> Filtern</button>
        <a class="btn btn-secondary" href="<?= base_url('flightmeet/meetups') ?>">Zurücksetzen</a>
        <div class="fm-checkbox-group">
            <?php foreach ($statusOptions as $status): ?>
                <label class="fm-checkbox">
                    <input
                            type="checkbox"
                            name="status[]"
                            value="<?= esc($status) ?>"
                            data-auto-submit="status"
                            <?= in_array($status, $selectedStatus, true) ? 'checked' : '' ?>
                    >
                    <span><?= esc(ucfirst($status)) ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</form>

<div class="fm-controls-bar">
    <div class="fm-view-toggle" role="group" aria-label="Darstellungsart wählen">
        <button class="fm-toggle-btn is-active" type="button" data-view="cards">Kachelansicht</button>
        <button class="fm-toggle-btn" type="button" data-view="table">Tabellenansicht</button>
    </div>

    <a href="<?= base_url('flightmeet/meetups/create') ?>" class="btn-new-meet">
        <i class="ph ph-plus"></i> Neues Treffen
    </a>
</div>

<section class="fm-section" id="fmCardsView">
    <?php if ($meetups === []): ?>
        <p class="fm-empty">Keine Flugtreffen für die aktuellen Filter gefunden.</p>
    <?php else: ?>
        <div class="fm-grid">
            <?php foreach ($meetups as $meetup): ?>
                <a class="fm-card fm-card--link" href="<?= base_url('flightmeet/meetups/' . $meetup['id']) ?>">
                    <header class="fm-card__header">
                        <h3><?= esc($meetup['title']) ?></h3>
                        <span class="fm-status fm-status--<?= esc($meetup['status']) ?>"><?= esc($meetup['status']) ?></span>
                    </header>
                    <p class="fm-card__desc"><?= esc($meetup['description']) ?></p>
                    <dl class="fm-card__meta">
                        <div>
                            <dt>Flugspot</dt>
                                <dd><?= esc($meetup['location']) ?></dd>
                        </div>
                        <div>
                            <dt>Region</dt>
                            <dd><?= esc($meetup['region']) ?></dd>
                        </div>
                        <div>
                            <dt>Datum</dt>
                            <dd><?= date('d.m.Y', strtotime($meetup['meet_date'])) ?></dd>
                        </div>
                        <div>
                            <dt>Uhrzeit</dt>
                            <dd><?= date('H:i', strtotime($meetup['meet_time'])) ?> Uhr</dd>
                        </div>
                        <div>
                            <dt>Erfahrungslevel</dt>
                                <dd><?= esc($meetup['experience_level']) ?></dd>
                        </div>
                        <div>
                            <dt>Teilnehmende</dt>
                                <dd><?= esc($meetup['participants_count'] ?? 0) ?> / <?= esc($meetup['max_participants']) ?></dd>
                        </div>
                    </dl>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="fm-section" id="fmTableView">
    <div class="fm-table-wrapper">
        <table class="fm-table">
            <thead>
            <tr>
                <!-- NEU: Klasse 'sort' und 'data-sort' hinzugefügt (Cursor als Zeiger für bessere Usability) -->
                <th class="sort" data-sort="col-title" style="cursor: pointer;">Titel <i class="ph ph-caret-up-down"></i></th>
                <th class="sort" data-sort="col-spot" style="cursor: pointer;">Flugspot <i class="ph ph-caret-up-down"></i></th>
                <th class="sort" data-sort="col-region" style="cursor: pointer;">Region <i class="ph ph-caret-up-down"></i></th>
                <th class="sort" data-sort="col-date" style="cursor: pointer;">Datum <i class="ph ph-caret-up-down"></i></th>
                <th class="col-time">Zeit</th>
                <th class="col-level">Level</th>
                <th class="col-count">Anzahl</th>
                <th class="col-status">Status</th>
            </tr>
            </thead>
            <!-- NEU: Klasse 'list' im tbody ergänzt -->
            <tbody class="list">
            <?php if ($meetups === []): ?>
                <tr>
                    <td colspan="9" class="fm-table__empty">Keine Flugtreffen für die aktuellen Filter gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($meetups as $meetup): ?>
                    <tr class="fm-row-link">
                        <!-- NEU: Klasse 'col-title' hinzugefügt -->
                        <td class="fm-row-link__cell col-title">
                            <a class="fm-row-link__anchor" href="<?= base_url('flightmeet/meetups/' . $meetup['id']) ?>">
                                <?= esc($meetup['title']) ?>
                            </a>
                        </td>
                        <td class="col-spot"><?= esc($meetup['location']) ?></td>
                        <td><span class="fm-badge-region col-region"><?= esc($meetup['region']) ?></span></td>
                        <!-- NEU: Klasse 'col-date' direkt auf das td-Element gelegt -->
                        <td class="fm-table__date col-date" data-timestamp="<?= strtotime($meetup['meet_date']) ?>">
                            <?= date('d.m.Y', strtotime($meetup['meet_date'])) ?>
                        </td>
                        <td class="fm-table__time col-time">
                            <?= date('H:i', strtotime($meetup['meet_time'])) ?>
                        </td>
                        <td>
                    <span class="fm-badge-level col-level fm-badge-level--<?= esc(strtolower($meetup['experience_level'])) ?>">
                        <?= esc($meetup['experience_level']) ?>
                    </span>
                        </td>
                        <td class="fm-table__participants col-count">
                            <strong><?= esc($meetup['participants_count'] ?? 0) ?></strong> <span class="fm-slash">/</span> <?= esc($meetup['max_participants']) ?>
                        </td>
                        <td>
                    <span class="fm-status col-status fm-status--<?= esc($meetup['status']) ?>">
                        <?= esc($meetup['status']) ?>
                    </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- List.js CDN laden -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>

<?= $this->endSection() ?>

