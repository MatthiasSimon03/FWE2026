<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

    <div class="fm-detail-layout">
        <!-- Linker Bereich: Aktivitäten-Tabs -->
        <div class="fm-detail-main">
            <h2 class="fm-section-title" style="margin-top: 0;">Meine Aktivitäten</h2>

            <div class="fm-view-toggle" style="margin-bottom: 20px; display: inline-flex;">
                <button class="fm-toggle-btn is-active" type="button" data-tab-target="tab-list">
                    <i class="ph ph-list-bullets" style="vertical-align: middle;"></i> Flugliste
                </button>
                <button class="fm-toggle-btn" type="button" data-tab-target="tab-map">
                    <i class="ph ph-map-trifold" style="vertical-align: middle;"></i> Flugkarte
                </button>
                <button class="fm-toggle-btn" type="button" data-tab-target="tab-calendar">
                    <i class="ph ph-calendar" style="vertical-align: middle;"></i> Kalender
                </button>
            </div>

            <!-- TAB 1: FLUGLISTE -->
            <div id="tab-list" class="tab-content">
                <div class="fm-view-toggle" style="margin-bottom: 15px; background-color: var(--color-bg-light); padding: 4px;">
                    <button class="fm-toggle-btn is-active" type="button" onclick="switchFlightTab('active')" style="font-size: 0.85rem;">Meine aktiven Flüge</button>
                    <button class="fm-toggle-btn" type="button" onclick="switchFlightTab('historic')" style="font-size: 0.85rem;">Meine Historie</button>
                </div>

                <div id="flights-active">
                    <?php if (empty($scheduled_flights)): ?>
                        <p class="fm-empty">Du bist aktuell bei keinen anstehenden Flügen angemeldet.</p>
                    <?php else: ?>
                        <div class="fm-grid" style="grid-template-columns: 1fr;">
                            <?php foreach ($scheduled_flights as $flight): ?>
                                <a href="<?= base_url('flightmeet/meetups/' . $flight['id']) ?>" class="fm-card fm-card--link" style="padding: 14px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                        <h4 style="margin:0; font-size: 1rem;"><?= esc($flight['title']) ?></h4>
                                        <span class="fm-status fm-status--<?= esc($flight['status']) ?>"><?= esc($flight['status']) ?></span>
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 20px; font-size: 0.85rem; color: var(--color-text-muted); margin-top: 4px; align-items: center;">
                                        <span style="display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-map-pin"></i> <?= esc($flight['location']) ?></span>
                                        <span style="display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-calendar"></i> <?= date('d.m.Y', strtotime($flight['meet_date'])) ?> um <?= date('H:i', strtotime($flight['meet_time'])) ?> Uhr</span>
                                        <span style="display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-user"></i> Ersteller: <?= (int)$flight['creator_is_private'] === 1 ? '🔒 Privat' : esc($flight['creator_name']) ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="flights-historic" style="display: none;">
                    <?php if (empty($historic_flights)): ?>
                        <p class="fm-empty">Keine vergangenen Flüge in deiner Historie gefunden.</p>
                    <?php else: ?>
                        <div class="fm-grid" style="grid-template-columns: 1fr;">
                            <?php foreach ($historic_flights as $flight): ?>
                                <a href="<?= base_url('flightmeet/meetups/' . $flight['id']) ?>" class="fm-card fm-card--link" style="padding: 14px; opacity: 0.85;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                        <h4 style="margin:0; font-size: 1rem; color: var(--color-text-muted-dark);"><?= esc($flight['title']) ?></h4>
                                        <span class="fm-status fm-status--<?= esc($flight['status']) ?>"><?= esc($flight['status']) ?></span>
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 20px; font-size: 0.85rem; color: var(--color-text-muted); margin-top: 4px; align-items: center;">
                                        <span style="display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-map-pin"></i> <?= esc($flight['location']) ?></span>
                                        <span style="display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-calendar"></i> <?= date('d.m.Y', strtotime($flight['meet_date'])) ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 2: MEINE FLUGKARTE -->
            <div id="tab-map" class="tab-content" style="display: none;">
                <div id="profile-flights-map"
                     style="height: 480px; width: 100%; border-radius: 12px; border: 1px solid var(--color-border-medium);"
                     data-flights='<?= json_encode($scheduled_flights) ?>'
                     data-base-url="<?= base_url() ?>"
                     data-icon-url="<?= base_url('assets/icons/paraglider.png') ?>">
                </div>
                <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 8px; text-align: center;">
                    <img width="16px" style="vertical-align: middle;" src="<?= base_url('assets/icons/paraglider.png') ?>"> markieren alle deine anstehenden Flugtreffen.
                </p>
            </div>

            <!-- TAB 3: KALENDERANSICHT -->
            <div id="tab-calendar" class="tab-content" style="display: none;">
                <div class="fm-calendar-container">
                    <div class="fm-calendar-header">
                        <button class="fm-calendar-month-btn" onclick="pCalendar.prev()">◀ Zurück</button>
                        <h3 id="p-cal-title" style="margin: 0; color: var(--color-text-title);"></h3>
                        <button class="fm-calendar-month-btn" onclick="pCalendar.next()">Weiter ▶</button>
                    </div>
                    <div class="fm-calendar-grid" id="p-cal-grid">
                        <!-- Wird per JS injiziert -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Rechte Sidebar: Benutzer-Profil & Gruppen -->
        <div class="fm-detail-sidebar">
            <div class="fm-detail-card">

                <!-- Überschrift mit dezentem Edit-Pencil -->
                <h3 class="fm-detail-card-title" style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 0; margin-bottom: 20px;">
                    <span>Mein Profil</span>
                    <button id="toggle-edit-profile" class="btn-action-edit" title="Profil bearbeiten" style="background: none; border: none; cursor: pointer; padding: 0;">
                        <i class="ph ph-pencil" style="font-size: 1.2rem;"></i>
                    </button>
                </h3>

                <!-- A: STANDARD-ANZEIGE-MODUS -->
                <div id="profile-display-view">
                    <dl class="fm-detail-info-list">
                        <div class="fm-detail-info-item">
                            <dt>Pilot</dt>
                            <dd><strong><?= esc($user['username']) ?></strong></dd>
                        </div>
                        <div class="fm-detail-info-item">
                            <dt>E-Mail</dt>
                            <dd><?= esc($user['email']) ?></dd>
                        </div>
                        <div class="fm-detail-info-item">
                            <dt>Erfahrung</dt>
                            <dd>
                                <?php $levelClass = strtolower(esc($user['experience_level'])); ?>
                                <span class="fm-badge-level fm-badge-level--<?= $levelClass ?>">
                                    <?= esc($user['experience_level']) ?>
                                </span>
                            </dd>
                        </div>

                        <!-- NEU: Pilot-Statistiken -->
                        <div class="fm-detail-info-item" style="border-top: 1px dashed var(--color-border-medium); padding-top: 12px; margin-top: 12px;">
                            <dt>Absolvierte Flüge</dt>
                            <dd><span class="fm-status fm-status--aktiv" style="font-weight: 700; font-size: 0.85rem; padding: 2px 10px;"><?= esc($stats['completed']) ?></span></dd>
                        </div>
                        <div class="fm-detail-info-item">
                            <dt>Organisierte Treffen</dt>
                            <dd><span class="fm-status fm-status--geplant" style="font-weight: 700; font-size: 0.85rem; padding: 2px 10px;"><?= esc($stats['created']) ?></span></dd>
                        </div>
                    </dl>
                </div>

                <!-- B: INLINE BEARBEITEN-MODUS (Umschaltbar per JS) -->
                <div id="profile-edit-view" style="display: none;">
                    <form method="post" action="<?= base_url('flightmeet/profile') ?>" class="fm-form-grid" style="gap: 12px;">
                        <?= csrf_field() ?>

                        <label class="fm-field">
                            <span class="fm-field__label" style="font-size: 0.75rem;">Benutzername</span>
                            <input class="fm-field__input" type="text" name="username" value="<?= esc($user['username']) ?>" required style="padding: 6px 10px; font-size: 0.9rem;">
                        </label>

                        <label class="fm-field">
                            <span class="fm-field__label" style="font-size: 0.75rem;">Erfahrungslevel</span>
                            <select class="fm-field__input" name="experience_level" required style="padding: 6px 10px; font-size: 0.9rem; background-color: white;">
                                <option value="Einsteiger" <?= $user['experience_level'] === 'Einsteiger' ? 'selected' : '' ?>>Einsteiger</option>
                                <option value="Fortgeschritten" <?= $user['experience_level'] === 'Fortgeschritten' ? 'selected' : '' ?>>Fortgeschritten</option>
                                <option value="Profi" <?= $user['experience_level'] === 'Profi' ? 'selected' : '' ?>>Profi</option>
                            </select>
                        </label>

                        <div style="display: flex; gap: 8px; margin-top: 12px;">
                            <button type="submit" class="btn" style="padding: 6px 10px; font-size: 0.85rem; flex: 1; border: none; cursor: pointer;">Speichern</button>
                            <button type="button" id="cancel-edit-profile" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.85rem; flex: 1; border: none; cursor: pointer;">Abbrechen</button>
                        </div>
                    </form>
                </div>

                <div class="card" style="margin-top: 20px; background: var(--color-bg-white); border-color: var(--color-border-card); padding: 16px;">
                    <h4 class="fm-sidebar-subtitle" style="margin: 0 0 12px 0;">Saison-Aktivität (<?= date('Y') ?>)</h4>
                    <canvas id="profileActiveChart" data-stats='<?= json_encode($months_data) ?>' style="max-height: 150px; width: 100%;"></canvas>
                </div>

                <hr class="fm-divider">

                <!-- Liste der Gruppen, in denen der Benutzer Mitglied ist -->
                <h4 class="fm-sidebar-subtitle">Meine Gruppen (<?= count($joined_groups) ?>)</h4>
                <?php if (empty($joined_groups)): ?>
                    <p class="fm-empty-text">Du bist aktuell in keiner Gruppe Mitglied.</p>
                <?php else: ?>
                    <ul class="fm-participants-list" style="max-height: 250px;">
                        <?php foreach ($joined_groups as $g): ?>
                            <li>
                                <span class="fm-participant-avatar">🏔️</span>
                                <span class="fm-participant-name">
                                <a href="<?= base_url('flightmeet/groups/detail/' . $g['id']) ?>" style="color: var(--color-text-title); font-weight: 500; text-decoration: none;">
                                    <?= esc($g['name']) ?>
                                </a>
                            </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 1. FLUG-LISTE-TABS (Geplant vs. Historisch)
        function switchFlightTab(tab) {
            const buttons = document.querySelectorAll('#tab-list .fm-view-toggle button');
            const activeFlightsDiv = document.getElementById('flights-active');
            const historicFlightsDiv = document.getElementById('flights-historic');

            if (tab === 'active') {
                activeFlightsDiv.style.display = 'block';
                historicFlightsDiv.style.display = 'none';
                buttons[0].classList.add('is-active');
                buttons[1].classList.remove('is-active');
            } else {
                activeFlightsDiv.style.display = 'none';
                historicFlightsDiv.style.display = 'block';
                buttons[0].classList.remove('is-active');
                buttons[1].classList.add('is-active');
            }
        }

        let pCalendar = null;

        document.addEventListener('DOMContentLoaded', () => {
            // Wartet in sehr kurzen Abständen, bis flightmeet.js geladen und bereit ist
            const checkInterval = setInterval(() => {
                if (typeof initDynamicFlightsMap !== 'undefined' && typeof DynamicFlightCalendar !== 'undefined') {
                    clearInterval(checkInterval); // Intervall stoppen, sobald Funktionen existieren

                    // Generische Map für das Profil initialisieren
                    initDynamicFlightsMap('profile-flights-map');

                    // Generischen Kalender für das Profil initialisieren
                    const calendarFlights = <?= json_encode(array_merge($scheduled_flights, $historic_flights)) ?>;
                    pCalendar = new DynamicFlightCalendar('p-cal', calendarFlights, '<?= base_url() ?>');
                    pCalendar.render();
                    initProfileChart();
                }
            }, 30); // Alle 30ms prüfen

            // Inline-Formular Umschalt-Logik per Klick auf das Stift-Icon
            const toggleBtn = document.getElementById('toggle-edit-profile');
            const cancelBtn = document.getElementById('cancel-edit-profile');
            const displayView = document.getElementById('profile-display-view');
            const editView = document.getElementById('profile-edit-view');

            toggleBtn?.addEventListener('click', () => {
                const isEditing = editView.style.display === 'block';
                displayView.style.display = isEditing ? 'block' : 'none';
                editView.style.display = isEditing ? 'none' : 'block';

                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.className = isEditing ? 'ph ph-pencil' : 'ph ph-user-circle';
                }
            });

            cancelBtn?.addEventListener('click', () => {
                displayView.style.display = 'block';
                editView.style.display = 'none';
                const icon = toggleBtn?.querySelector('i');
                if (icon) icon.className = 'ph ph-pencil';
            });
        });function initProfileChart() {
            const chartEl = document.getElementById('profileActiveChart');
            if (chartEl && typeof Chart !== 'undefined') {
                const rawData = JSON.parse(chartEl.dataset.stats || '[]');
                const ctx = chartEl.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
                        datasets: [{
                            data: rawData,
                            backgroundColor: 'rgba(30, 136, 229, 0.15)',
                            borderColor: 'rgba(30, 136, 229, 1)',
                            borderWidth: 1.5,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, color: '#64748b', font: { size: 9 } },
                                grid: { display: false }
                            },
                            x: {
                                ticks: { color: '#64748b', font: { size: 9 } },
                                grid: { display: false }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        }
    </script>

<?= $this->endSection() ?>