<?= $this->extend('FlightMeet/layout') ?>

<?= $this->section('content') ?>
<h1>Flugtreffen</h1>
<p class="lead">
    Entdecke anstehende Flugtreffen und verabrede dich mit anderen Interessierten.
</p>

<?php $meetups = $meetups ?? []; ?>
<?php $filters = $filters ?? ['q' => '', 'region' => '', 'level' => '']; ?>
<?php $options = $options ?? ['regions' => [], 'levels' => []]; ?>

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
        <button class="btn" type="submit">Filtern</button>
        <a class="btn btn-secondary" href="<?= base_url('flightmeet/meetups') ?>">Zurücksetzen</a>
    </div>
</form>

<div class="fm-view-toggle" role="group" aria-label="Darstellungsart wählen">
    <button class="fm-toggle-btn is-active" type="button" data-view="cards">Kartenansicht</button>
    <button class="fm-toggle-btn" type="button" data-view="table">Tabellenansicht</button>
</div>

<section class="fm-section" id="fmCardsView">
    <h2>Kartenansicht</h2>
    <?php if ($meetups === []): ?>
        <p class="fm-empty">Keine Flugtreffen für die aktuellen Filter gefunden.</p>
    <?php else: ?>
        <div class="fm-grid">
            <?php foreach ($meetups as $meetup): ?>
                <article class="fm-card">
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
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="fm-section" id="fmTableView">
    <h2>Tabellenansicht</h2>
    <div class="fm-table-wrapper">
        <table class="fm-table">
            <thead>
            <tr>
                <th>Titel</th>
                <th>Flugspot</th>
                <th>Region</th>
                <th>Beschreibung</th>
                <th>Datum</th>
                <th>Zeit</th>
                <th>Level</th>
                <th>Anzahl</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($meetups === []): ?>
                <tr>
                    <td colspan="9" class="fm-table__empty">Keine Flugtreffen für die aktuellen Filter gefunden.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($meetups as $meetup): ?>
                    <tr>
                        <td class="fm-table__title"><?= esc($meetup['title']) ?></td>
                        <td><?= esc($meetup['location']) ?></td>
                        <td><span class="fm-badge-region"><?= esc($meetup['region']) ?></span></td>
                        <td>
                            <div class="fm-table__desc" title="<?= esc($meetup['description']) ?>">
                                <?= esc($meetup['description']) ?>
                            </div>
                        </td>
                        <td class="fm-table__date">
                            <?= date('d.m.Y', strtotime($meetup['meet_date'])) ?>
                        </td>
                        <td class="fm-table__time">
                            <?= date('H:i', strtotime($meetup['meet_time'])) ?>
                        </td>
                        <td>
                            <span class="fm-badge-level fm-badge-level--<?= esc(strtolower($meetup['experience_level'])) ?>">
                                <?= esc($meetup['experience_level']) ?>
                            </span>
                        </td>
                        <td class="fm-table__participants">
                            <strong><?= esc($meetup['participants_count'] ?? 0) ?></strong> <span class="fm-slash">/</span> <?= esc($meetup['max_participants']) ?>
                        </td>
                        <td>
                            <span class="fm-status fm-status--<?= esc($meetup['status']) ?>">
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
<?= $this->endSection() ?>

