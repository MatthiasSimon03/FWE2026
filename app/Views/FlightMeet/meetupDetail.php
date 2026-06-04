<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <h1><?= esc($meetup['title']) ?></h1>
    <p class="lead"><?= esc($meetup['description']) ?></p>

    <ul class="fm-detail-meta">
        <li><strong>Flugspot:</strong> <?= esc($meetup['location']) ?></li>
        <li><strong>Region:</strong> <?= esc($meetup['region']) ?></li>
        <li><strong>Datum/Zeit:</strong> <?= date('d.m.Y', strtotime($meetup['meet_date'])) ?>, <?= date('H:i', strtotime($meetup['meet_time'])) ?> Uhr</li>
        <li><strong>Erfahrungslevel:</strong> <?= esc($meetup['experience_level']) ?></li>
        <li><strong>Freie Plätze:</strong> <?= esc($meetup['free_slots']) ?></li>
    </ul>

    <h2>Teilnehmende</h2>
<?php if (empty($meetup['participants'])): ?>
    <p>Noch keine Teilnehmenden.</p>
<?php else: ?>
    <ul>
        <?php foreach ($meetup['participants'] as $p): ?>
            <li><?= esc($p['username']) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

    <div class="actions">
        <button class="btn">Teilnehmen</button>
        <a class="btn btn-secondary" href="<?= base_url('flightmeet/meetups') ?>">Zurück</a>
    </div>

    <h2>Karte</h2>
    <div id="map" style="height: 320px;"></div>
    <script>
        // Beispiel mit Leaflet (OSM). Du brauchst leaflet.css/js im Layout.
        const map = L.map('map').setView([47.0, 11.0], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap-Mitwirkende'
        }).addTo(map);
        // TODO: Koordinaten für den Flugspot setzen
    </script>

<?= $this->endSection() ?>