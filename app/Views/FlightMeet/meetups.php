<?= $this->extend('FlightMeet/layout') ?>

<?= $this->section('content') ?>
<h1>Flugtreffen</h1>
<p class="lead">
    Entdecke anstehende Flugtreffen und verabrede dich mit anderen Interessierten.
</p>

<?php $meetups = $meetups ?? []; ?>

<div class="fm-view-toggle" role="group" aria-label="Darstellungsart wählen">
    <button class="fm-toggle-btn is-active" type="button" data-view="cards">Kartenansicht</button>
    <button class="fm-toggle-btn" type="button" data-view="table">Tabellenansicht</button>
</div>

<section class="fm-section" id="fmCardsView">
    <h2>Kartenansicht</h2>
    <div class="fm-grid">
        <?php foreach ($meetups as $meetup): ?>
            <article class="fm-card">
                <header class="fm-card__header">
                    <h3><?= esc($meetup['title']) ?></h3>
                    <span class="fm-status fm-status--<?= esc($meetup['status']) ?>"><?= esc($meetup['status']) ?></span>
                </header>
                <dl class="fm-card__meta">
                    <div>
                        <dt>Flugspot</dt>
                        <dd><?= esc($meetup['spot']) ?></dd>
                    </div>
                    <div>
                        <dt>Region</dt>
                        <dd><?= esc($meetup['region']) ?></dd>
                    </div>
                    <div>
                        <dt>Datum</dt>
                        <dd><?= esc($meetup['date']) ?></dd>
                    </div>
                    <div>
                        <dt>Uhrzeit</dt>
                        <dd><?= esc($meetup['time']) ?></dd>
                    </div>
                    <div>
                        <dt>Erfahrungslevel</dt>
                        <dd><?= esc($meetup['level']) ?></dd>
                    </div>
                    <div>
                        <dt>Teilnehmende</dt>
                        <dd><?= esc($meetup['participants']) ?> / <?= esc($meetup['max_participants']) ?></dd>
                    </div>
                </dl>
            </article>
        <?php endforeach; ?>
    </div>
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
                <th>Datum</th>
                <th>Uhrzeit</th>
                <th>Erfahrungslevel</th>
                <th>Teilnehmende</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($meetups as $meetup): ?>
                <tr>
                    <td><?= esc($meetup['title']) ?></td>
                    <td><?= esc($meetup['spot']) ?></td>
                    <td><?= esc($meetup['region']) ?></td>
                    <td><?= esc($meetup['date']) ?></td>
                    <td><?= esc($meetup['time']) ?></td>
                    <td><?= esc($meetup['level']) ?></td>
                    <td><?= esc($meetup['participants']) ?> / <?= esc($meetup['max_participants']) ?></td>
                    <td><?= esc($meetup['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?= $this->endSection() ?>

