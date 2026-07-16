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

    <!-- Globale Erfolgs- und Fehlermeldungen (z. B. nach Anmeldung/Abmeldung) -->
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

    <!-- Zweispaltiges Detail-Layout -->
    <div class="fm-detail-layout">

        <!-- LINKER BEREICH: Hauptinhalt, Karte und Wetter -->
        <div class="fm-detail-main">

            <div class="fm-detail-header">
                <h1 class="fm-detail-title" style="margin: 0;"><?= esc($meetup['title']) ?></h1>
            </div>

            <p class="lead fm-detail-desc"><?= esc($meetup['description']) ?></p>

            <!-- Startplatz-Karte -->
            <div class="fm-detail-map-section">
                <h2 class="fm-section-title">Karte / Startplatz</h2>
                <div id="map"></div>
            </div>

            <!-- Wettervorhersage: Platziert im breiteren Hauptbereich unter der Karte -->
            <div class="fm-detail-weather-section" style="margin-top: 30px;">
                <h2 class="fm-section-title" style="display: flex; align-items: center; gap: 8px;">
                    <i class="ph ph-cloud-sun" style="font-size: 1.3rem; color: var(--color-primary);"></i>
                    <span>Wettervorhersage für den Startplatz</span>
                </h2>

                <div class="fm-detail-card" style="margin-top: 10px;">
                    <!-- Lade-Indikator während des API-Requests -->
                    <div id="weather-loading" style="color: var(--color-text-muted); font-size: 0.9rem;">
                        <i class="ph ph-spinner-gap weather-spin"></i> Lade Wetterdaten...
                    </div>

                    <!-- Horizontales Grid zur optimalen Platzausnutzung -->
                    <div id="weather-info" style="display: none;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; align-items: center;">

                            <!-- Temperatur & Kurzbeschreibung -->
                            <div style="display: flex; align-items: center; gap: 20px;">
                                <i id="weather-icon" class="ph" style="font-size: 3.5rem; color: var(--color-primary);"></i>
                                <div>
                                    <span id="weather-temp" style="font-size: 1.8rem; font-weight: 700; color: var(--color-text-title); line-height: 1.1;"></span>
                                    <p id="weather-desc" style="margin: 6px 0 0 0; font-size: 0.95rem; color: var(--color-text-muted-dark); font-weight: 500;"></p>
                                </div>
                            </div>

                            <!-- Windstatistiken -->
                            <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; border-bottom: 1px dashed var(--color-border-medium); padding-bottom: 6px;">
                                    <span style="color: var(--color-text-muted);">Max. Windgeschwindigkeit:</span>
                                    <strong id="weather-wind"></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; border-bottom: 1px dashed var(--color-border-medium); padding-bottom: 6px;">
                                    <span style="color: var(--color-text-muted);">Stärkste Windböen:</span>
                                    <strong id="weather-gusts"></strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; padding-bottom: 2px;">
                                    <span style="color: var(--color-text-muted);">Hauptwindrichtung:</span>
                                    <strong id="weather-direction"></strong>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Fehlermeldung bei API-Problemen oder abgelaufenen Terminen -->
                    <p id="weather-error" style="display: none; color: var(--color-text-muted-light); font-size: 0.85rem; font-style: italic; margin: 0;"></p>
                </div>
            </div>

        </div>

        <!-- RECHTER BEREICH: Sidebar -->
        <div class="fm-detail-sidebar">

            <!-- Kachel 1: Treffen-Details und An-/Abmeldeaktionen -->
            <div class="fm-detail-card">
                <h3 class="fm-detail-card-title" style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 0; margin-bottom: 20px;">
                    <span>Treffen-Details</span>
                    <!-- Administrative Aktionen: Nur für den Ersteller des Treffens sichtbar -->
                    <?php if ((int)$meetup['creator_id'] === (int)session()->get('fm_user_id')): ?>
                        <div class="fm-detail-actions">
                            <a href="<?= base_url('flightmeet/meetups/edit/' . $meetup['id']) ?>"
                               class="btn-action-edit"
                               title="Flugtreffen bearbeiten">
                                <i class="ph ph-pencil" style="font-size: 1.2rem;"></i>
                            </a>

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

                <!-- Metadaten-Liste -->
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
                            <?php $levelClass = strtolower(esc($meetup['experience_level'])); ?>
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

                <!-- Aktions-Buttons: Anmeldung / Abmeldung / Navigation -->
                <div class="fm-sidebar-actions">
                    <?php if ($meetup['status'] === 'geplant'): ?>
                        <?php if ($meetup['is_participating']): ?>
                            <form method="post" action="<?= base_url('flightmeet/meetups/leave/' . $meetup['id']) ?>" style="width:100%;">
                                <?= csrf_field() ?>
                                <button class="btn-danger-full" type="submit">Teilnahme Absagen</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?= base_url('flightmeet/meetups/join/' . $meetup['id']) ?>" style="width:100%;">
                                <?= csrf_field() ?>
                                <button class="btn btn-primary-full" type="submit">Teilnehmen</button>
                            </form>
                        <?php endif; ?>

                    <?php elseif ($meetup['status'] === 'ausgebucht' && $meetup['is_participating']): ?>
                        <form method="post" action="<?= base_url('flightmeet/meetups/leave/' . $meetup['id']) ?>" style="width:100%;">
                            <?= csrf_field() ?>
                            <button class="btn-danger-full" type="submit">Absagen</button>
                        </form>
                    <?php endif; ?>

                    <!-- Dynamischer Zurück-Link (abhängig vom HTTP-Referer/Verlauf) -->
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
            </div>

            <!-- Kachel 2: Auflistung aller angemeldeten Teilnehmer -->
            <div class="fm-detail-card" style="margin-top: 20px;">
                <h3 class="fm-detail-card-title" style="margin-top: 0; margin-bottom: 16px;">
                    Teilnehmende (<?= count($meetup['participants']) ?>)
                </h3>
                <?php if (empty($meetup['participants'])): ?>
                    <p class="fm-empty-text" style="margin-bottom: 0;">Noch keine Teilnehmer angemeldet.</p>
                <?php else: ?>
                    <ul class="fm-participants-list" style="margin-bottom: 0;">
                        <?php foreach ($meetup['participants'] as $p): ?>
                            <li>
                                <span class="fm-participant-avatar">👤</span>
                                <span class="fm-participant-name"><?= esc($p['username']) ?></span>
                                <!-- Chat-Icon: Ermöglicht direkte Kontaktaufnahme (außer bei sich selbst) -->
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

        </div>
    </div>

    <script>
        // Leaflet-Karte initialisieren und Startplatz-Marker setzen
        const map = L.map('map').setView([<?= esc($meetup['latitude']) ?>, <?= esc($meetup['longitude']) ?>], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap-Mitwirkende'
        }).addTo(map);
        var paragliderIcon = L.icon({
            iconUrl: '<?= base_url('assets/icons/paraglider.png') ?>',
            iconSize: [40, 40],
            iconAnchor: [20, 40],
            popupAnchor: [0, -40]
        });
        var marker = L.marker([<?= esc($meetup['latitude']) ?>, <?= esc($meetup['longitude']) ?>], {icon: paragliderIcon}).addTo(map);

        // Konvertiert numerische Windgrade in Himmelsrichtungen (N, NO, etc.)
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

            // Sicherheitsbarriere: Wetterdaten sind über die Open-Meteo-API erst 14 Tage vor dem Termin verfügbar
            if (diffDays > 14) {
                loadingEl.style.display = 'none';
                errorEl.style.display = 'block';
                errorEl.innerText = "🌤️ Vorhersage erst ab 14 Tage vor dem Treffen verfügbar.";
                return;
            }

            // API-Anfrage an Open-Meteo absenden
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
                    const windMax = Math.round(data.daily.wind_speed_10m_max[0]);
                    const gustsMax = data.daily.wind_gusts_10m_max ? Math.round(data.daily.wind_gusts_10m_max[0]) : null;
                    const windDirDeg = data.daily.wind_direction_10m_dominant ? data.daily.wind_direction_10m_dominant[0] : null;

                    // Mapping-Tabelle für WMO-Wettercodes auf Beschreibungen und Icons
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

                    // DOM-Elemente befüllen
                    document.getElementById('weather-temp').innerText = `${tempMax}°C / ${tempMin}°C`;
                    document.getElementById('weather-desc').innerText = wDetails.desc;
                    document.getElementById('weather-wind').innerText = `${windMax} km/h`;

                    if (gustsMax !== null) {
                        document.getElementById('weather-gusts').innerText = `${gustsMax} km/h`;
                    } else {
                        document.getElementById('weather-gusts').innerText = '--';
                    }

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