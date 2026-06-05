<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <div class="fm-detail-layout">

        <!-- Linker Bereich: Hauptinhalt & Karte -->
        <div class="fm-detail-main">
            <h1 class="fm-detail-title"><?= esc($meetup['title']) ?></h1>
            <p class="lead fm-detail-desc"><?= esc($meetup['description']) ?></p>

            <div class="fm-detail-map-section">
                <h2 class="fm-section-title">Karte / Startplatz</h2>
                <div id="map"></div>
            </div>
        </div>

        <!-- Rechter Bereich: Info-Card, Teilnehmer & Aktionen -->
        <div class="fm-detail-sidebar">
            <div class="fm-detail-card">
                <h3 class="fm-detail-card-title">Treffen-Details</h3>

                <dl class="fm-detail-info-list">
                    <div class="fm-detail-info-item">
                        <dt><i class="ph ph-map-pin icons-meetup-detail"></i></dt>
                        <dd><strong><?= esc($meetup['location']) ?></strong></dd>
                    </div>

                    <div class="fm-detail-info-item">
                        <dt><i class="ph ph-compass icons-meetup-detail"></i></dt>
                        <dd><span class="fm-badge-region"><?= esc($meetup['region']) ?></span></dd>
                    </div>

                    <div class="fm-detail-info-item">
                        <dt><i class="ph ph-calendar-blank icons-meetup-detail"></i></dt>
                        <dd>
                            <span class="fm-table__date"><?= date('d.m.Y', strtotime($meetup['meet_date'])) ?></span>
                            <span class="fm-table__time">um <?= date('H:i', strtotime($meetup['meet_time'])) ?> Uhr</span>
                        </dd>
                    </div>

                    <div class="fm-detail-info-item">
                        <dt><i class="ph ph-medal icons-meetup-detail"></i></dt>
                        <dd>
                            <?php
                            // Erzeugt dynamisch den Klassennamen (z.B. fm-badge-level--einsteiger)
                            $levelClass = strtolower(esc($meetup['experience_level']));
                            ?>
                            <span class="fm-badge-level fm-badge-level--<?= $levelClass ?>">
                            <?= esc($meetup['experience_level']) ?>
                        </span>
                        </dd>
                    </div>

                    <div class="fm-detail-info-item">
                        <dt><i class="ph ph-users icons-meetup-detail"></i></dt>
                        <dd>
                            <?php if ($meetup['status'] === 'abgesagt'): ?>
                                <span class="fm-status fm-status--abgesagt">Abgesagt</span>
                            <?php elseif ($meetup['status'] === 'abgeschlossen'): ?>
                                <span class="fm-status fm-status--abgeschlossen">Abgeschlossen</span>
                            <?php elseif ($meetup['free_slots'] <= 0): ?>
                                <span class="fm-status fm-status--ausgebucht">Ausgebucht</span>
                            <?php else: ?>
                                <span class="fm-status fm-status--geplant"><?= esc($meetup['free_slots']) ?> Plätze frei</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>

                <hr class="fm-divider">

                <!-- Buttons -->
                <div class="fm-sidebar-actions">
                    <?php if ($meetup['status'] === 'geplant'): ?>
                        <button class="btn btn-primary-full">Teilnehmen</button>
                    <?php endif; ?>
                    <a class="btn-secondary-full" href="<?= base_url('flightmeet/meetups') ?>">Zurück zur Übersicht</a>
                </div>

                <hr class="fm-divider">

                <!-- Teilnehmer-Sektion -->
                <h4 class="fm-sidebar-subtitle">Teilnehmende (<?= count($meetup['participants']) ?>)</h4>
                <?php if (empty($meetup['participants'])): ?>
                    <p class="fm-empty-text">Noch keine Teilnehmer angemeldet.</p>
                <?php else: ?>
                    <ul class="fm-participants-list">
                        <?php foreach ($meetup['participants'] as $p): ?>
                            <li>
                                <span class="fm-participant-avatar">👤</span>
                                <span class="fm-participant-name"><?= esc($p['username']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const map = L.map('map').setView([<?= esc($meetup['latitude']) ?>, <?= esc($meetup['longitude']) ?>], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap-Mitwirkende'
        }).addTo(map);
        var marker = L.marker([<?= esc($meetup['latitude']) ?>, <?= esc($meetup['longitude']) ?>]).addTo(map);
    </script>

<?= $this->endSection() ?>