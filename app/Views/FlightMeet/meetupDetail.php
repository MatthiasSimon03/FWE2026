<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <style>
        /* Rotations-Animation für den Lade-Spinner */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .weather-spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
    </style>

    <!-- NEU: Globale Feedback-Meldungen (Erfolg / Fehler) -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

    <div class="fm-detail-layout">
        <!-- Linker Bereich: Hauptinhalt & Karte -->
        <div class="fm-detail-main">

            <!-- Titel-Header mit Flex-Layout für die Icons -->
            <div class="fm-detail-header">
                <h1 class="fm-detail-title" style="margin: 0;"><?= esc($meetup['title']) ?></h1>
            </div>

            <p class="lead fm-detail-desc"><?= esc($meetup['description']) ?></p>

            <div class="fm-detail-map-section">
                <h2 class="fm-section-title">Karte / Startplatz</h2>
                <div id="map"></div>
            </div>
        </div>

        <!-- Rechter Bereich: Info-Card, Wetter, Teilnehmer & Aktionen -->
        <div class="fm-detail-sidebar">
            <div class="fm-detail-card">

                <!-- Treffen-Details Überschrift mit den Aktions-Buttons daneben -->
                <h3 class="fm-detail-card-title" style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 0; margin-bottom: 20px;">
                    <span>Treffen-Details</span>
                    <?php if ((int)$meetup['creator_id'] === (int)session()->get('fm_user_id')): ?>
                        <div class="fm-detail-actions">
                            <!-- Bearbeiten Icon -->
                            <a href="<?= base_url('flightmeet/meetups/edit/' . $meetup['id']) ?>"
                               class="btn-action-edit"
                               title="Flugtreffen bearbeiten">
                                <i class="ph ph-pencil" style="font-size: 1.2rem;"></i>
                            </a>

                            <!-- Löschen Formular + Icon -->
                            <form method="post" action="<?= base_url('flightmeet/meetups/delete/' . $meetup['id']) ?>"
                                  onsubmit="return confirm('Möchten Sie dieses Flugtreffen wirklich löschen? Alle Anmeldungen gehen dabei verloren.');"
                                  style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-action-delete" title="Flugtreffen löschen">
                                    <i class="ph ph-trash" style="font-size: 1.2rem;"></i>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </h3>

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
                    <!-- Fall 1: Das Treffen ist regulär geplant -->
                    <?php if ($meetup['status'] === 'geplant'): ?>
                        <?php if ($meetup['is_participating']): ?>
                            <!-- User nimmt bereits teil -> Absagen -->
                            <form method="post" action="<?= base_url('flightmeet/meetups/leave/' . $meetup['id']) ?>" style="width:100%;">
                                <?= csrf_field() ?>
                                <button class="btn-danger-full" type="submit">Teilnahme Absagen</button>
                            </form>
                        <?php else: ?>
                            <!-- User nimmt noch nicht teil -> Teilnehmen -->
                            <form method="post" action="<?= base_url('flightmeet/meetups/join/' . $meetup['id']) ?>" style="width:100%;">
                                <?= csrf_field() ?>
                                <button class="btn btn-primary-full" type="submit">Teilnehmen</button>
                            </form>
                        <?php endif; ?>

                        <!-- Fall 2: Das Treffen ist ausgebucht, aber der angemeldete User nimmt teil -->
                    <?php elseif ($meetup['status'] === 'ausgebucht' && $meetup['is_participating']): ?>
                        <form method="post" action="<?= base_url('flightmeet/meetups/leave/' . $meetup['id']) ?>" style="width:100%;">
                            <?= csrf_field() ?>
                            <button class="btn-danger-full" type="submit">Absagen</button>
                        </form>
                    <?php endif; ?>

                    <!-- Link zurück zur Übersicht (dynamisch je nach Herkunft) -->
                    <?php if ($from_page === 'home'): ?>
                        <a class="btn-secondary-full" href="<?= site_url('flightmeet') ?>">
                            Zurück zur Startseite
                        </a>
                    <?php elseif (!empty($from_group)): ?>
                        <a class="btn-secondary-full" href="<?= base_url('flightmeet/groups/detail/' . esc($from_group)) ?>">
                            Zurück zur Gruppe
                        </a>
                    <?php else: ?>
                        <a class="btn-secondary-full" href="<?= base_url('flightmeet/meetups') ?>">
                            Zurück zur Übersicht
                        </a>
                    <?php endif; ?>
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
                                <?php if ((int)$p['id'] !== (int)session()->get('fm_user_id')): ?>
                                    <span class="fm-participant-mail">
                                        <a style="text-decoration: none;" href="<?= base_url('flightmeet/chat') ?>"><i class="ph ph-envelope icons-meetup-mail"></i> </a>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- NEU: INTERAKTIVE WETTER-KARTE -->
            <div class="fm-detail-card" style="margin-top: 20px;">
                <h3 class="fm-detail-card-title" style="display: flex; align-items: center; gap: 8px; margin: 0 0 16px 0;">
                    <i class="ph ph-cloud-sun" style="font-size: 1.3rem; color: var(--color-primary);"></i>
                    <span>Wettervorhersage</span>
                </h3>

                <div id="weather-loading" style="color: var(--color-text-muted); font-size: 0.9rem;">
                    <i class="ph ph-spinner-gap weather-spin"></i> Lade Wetterdaten...
                </div>

                <div id="weather-info" style="display: none;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                        <i id="weather-icon" class="ph" style="font-size: 2.5rem; color: var(--color-primary);"></i>
                        <div>
                            <span id="weather-temp" style="font-size: 1.4rem; font-weight: 700; color: var(--color-text-title);"></span>
                            <p id="weather-desc" style="margin: 0; font-size: 0.9rem; color: var(--color-text-muted-dark);"></p>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem; border-top: 1px dashed var(--color-border-medium); padding-top: 12px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--color-text-muted);">Max. Windgeschwindigkeit:</span>
                            <strong id="weather-wind"></strong>
                        </div>
                    </div>
                    <!-- NEU: Windböen -->
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-text-muted);">Stärkste Windböen:</span>
                        <strong id="weather-gusts"></strong>
                    </div>
                    <!-- NEU: Windrichtung -->
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-text-muted);">Hauptwindrichtung:</span>
                        <strong id="weather-direction"></strong>
                    </div>
                </div>

                <p id="weather-error" style="display: none; color: var(--color-text-muted-light); font-size: 0.85rem; font-style: italic; margin: 0;"></p>
            </div>
        </div>
    </div>

    <script>
        // LEAFLET MAP INITIALISIERUNG
        const map = L.map('map').setView([<?= esc($meetup['latitude']) ?>, <?= esc($meetup['longitude']) ?>], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap-Mitwirkende'
        }).addTo(map);
        var paragliderIcon = L.icon({
            iconUrl: '<?= base_url('assets/icons/paraglider.png') ?>',

            iconSize: [40, 40],      // Breite, Höhe
            iconAnchor: [20, 40],    // Punkt des Icons auf der Koordinate
            popupAnchor: [0, -40]    // Position des Popups relativ zum Icon
        });
        var marker = L.marker([<?= esc($meetup['latitude']) ?>, <?= esc($meetup['longitude']) ?>], {icon: paragliderIcon}).addTo(map);

        // Hilfsfunktion zur Umrechnung von Grad in Himmelsrichtungen
        function getWindDirection(degree) {
            const directions = ['N', 'NO', 'O', 'SO', 'S', 'SW', 'W', 'NW'];
            const index = Math.round(((degree % 360) / 45)) % 8;
            return directions[index];
        }

        document.addEventListener('DOMContentLoaded', () => {
            const lat = <?= esc($meetup['latitude']) ?>;
            const lng = <?= esc($meetup['longitude']) ?>;
            const meetDate = '<?= esc($meetup['meet_date']) ?>';

            const loadingEl = document.getElementById('weather-loading');
            const infoEl = document.getElementById('weather-info');
            const errorEl = document.getElementById('weather-error');

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const targetDate = new Date(meetDate);
            targetDate.setHours(0, 0, 0, 0);

            const diffTime = targetDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 14) {
                loadingEl.style.display = 'none';
                errorEl.style.display = 'block';
                errorEl.innerText = "🌤️ Vorhersage erst ab 14 Tage vor dem Treffen verfügbar.";
                return;
            }

            // API URL mit wind_gusts_10m_max und wind_direction_10m_dominant erweitert
            const apiUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&daily=weather_code,temperature_2m_max,temperature_2m_min,wind_speed_10m_max,wind_gusts_10m_max,wind_direction_10m_dominant&timezone=auto&start_date=${meetDate}&end_date=${meetDate}`;

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Netzwerk-Antwort war nicht ok');
                    return response.json();
                })
                .then(data => {
                    if (!data.daily || !data.daily.weather_code) {
                        throw new Error('Keine Wetterdaten verfügbar');
                    }

                    const weatherCode = data.daily.weather_code[0];
                    const tempMax = Math.round(data.daily.temperature_2m_max[0]);
                    const tempMin = Math.round(data.daily.temperature_2m_min[0]);

                    // Neue Winddaten auslesen
                    const windMax = Math.round(data.daily.wind_speed_10m_max[0]);
                    const gustsMax = data.daily.wind_gusts_10m_max ? Math.round(data.daily.wind_gusts_10m_max[0]) : null;
                    const windDirDeg = data.daily.wind_direction_10m_dominant ? data.daily.wind_direction_10m_dominant[0] : null;

                    const weatherMap = {
                        0: { desc: 'Sonnig/Wolkenlos', icon: 'ph-sun' },
                        1: { desc: 'Meist klar', icon: 'ph-sun' },
                        2: { desc: 'Teilweise bewölkt', icon: 'ph-cloud-sun' },
                        3: { desc: 'Bedeckt', icon: 'ph-cloud' },
                        45: { desc: 'Nebel', icon: 'ph-cloud-fog' },
                        48: { desc: 'Raureifnebel', icon: 'ph-cloud-fog' },
                        51: { desc: 'Leichter Sprühregen', icon: 'ph-cloud-rain' },
                        53: { desc: 'Mäßiger Sprühregen', icon: 'ph-cloud-rain' },
                        55: { desc: 'Dichter Sprühregen', icon: 'ph-cloud-rain' },
                        61: { desc: 'Leichter Regen', icon: 'ph-cloud-rain' },
                        63: { desc: 'Mäßiger Regen', icon: 'ph-cloud-rain' },
                        65: { desc: 'Starker Regen', icon: 'ph-cloud-heavy-rain' },
                        71: { desc: 'Leichter Schneefall', icon: 'ph-snowflake' },
                        73: { desc: 'Mäßiger Schneefall', icon: 'ph-snowflake' },
                        75: { desc: 'Starker Schneefall', icon: 'ph-snowflake' },
                        80: { desc: 'Leichte Regenschauer', icon: 'ph-cloud-rain' },
                        81: { desc: 'Mäßige Regenschauer', icon: 'ph-cloud-rain' },
                        82: { desc: 'Starke Regenschauer', icon: 'ph-cloud-heavy-rain' },
                        95: { desc: 'Gewitter', icon: 'ph-cloud-lightning' }
                    };

                    const wDetails = weatherMap[weatherCode] || { desc: 'Bedeckt', icon: 'ph-cloud' };

                    // Elemente befüllen
                    document.getElementById('weather-temp').innerText = `${tempMax}°C / ${tempMin}°C`;
                    document.getElementById('weather-desc').innerText = wDetails.desc;
                    document.getElementById('weather-wind').innerText = `${windMax} km/h`;

                    // Böen anzeigen
                    if (gustsMax !== null) {
                        document.getElementById('weather-gusts').innerText = `${gustsMax} km/h`;
                    } else {
                        document.getElementById('weather-gusts').innerText = '--';
                    }

                    // Windrichtung übersetzen und anzeigen
                    if (windDirDeg !== null) {
                        const dirText = getWindDirection(windDirDeg);
                        document.getElementById('weather-direction').innerText = `${dirText} (${windDirDeg}°)`;
                    } else {
                        document.getElementById('weather-direction').innerText = '--';
                    }

                    const iconEl = document.getElementById('weather-icon');
                    iconEl.className = `ph ${wDetails.icon}`;

                    loadingEl.style.display = 'none';
                    infoEl.style.display = 'block';
                })
                .catch(err => {
                    console.error('Fehler beim Laden des Wetters:', err);
                    loadingEl.style.display = 'none';
                    errorEl.style.display = 'block';
                    errorEl.innerText = "⚠️ Wetterdaten konnten nicht geladen werden.";
                });
        });
    </script>

<?= $this->endSection() ?>