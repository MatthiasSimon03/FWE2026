<?= $this->extend('FlightMeet/layout') ?>

<?= $this->section('content') ?>

    <!-- Willkommens-Bereich mit dynamischer Anrede -->
    <div class="fm-detail-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="fm-detail-title" style="margin-bottom: 4px; display: flex; align-items: center; gap: 12px;">
                Hallo, <?= esc($username) ?>!
            </h1>
            <p class="lead" style="margin: 0;">Willkommen zurück auf FlightMeet. Bereit für den nächsten Flug?</p>
        </div>
    </div>

    <!-- Zweispaltiges Layout für das Dashboard -->
    <div class="fm-detail-layout">

        <!-- LINKE SPALTE: Hauptinhalt -->
        <div style="display: flex; flex-direction: column; gap: 24px;">

            <!-- Kachel 1: Aktuelle Aktivitäten aus beigetretenen Gruppen -->
            <div class="fm-dashboard-chart-card" style="max-width: 100%; margin-top: 0;">

                <h3 style="display: flex; align-items: center; gap: 8px; margin-top: 0; margin-bottom: 4px;">
                    <i class="ph ph-bell" style="color: var(--color-primary); font-size: 1.3rem;"></i>
                    Neu in deinen Gruppen
                </h3>

                <p style="margin: 0 0 16px 0; font-size: 0.85rem; color: var(--color-text-muted-dark); line-height: 1.4;">
                    Die 3 zuletzt erstellten Flugtreffen von Pilotinnen und Piloten aus deinen Gruppen.
                </p>

                <?php if (!empty($latestGroupMeetups)): ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($latestGroupMeetups as $meet): ?>
                            <a href="<?= site_url('flightmeet/meetups/' . $meet['id'] . '?from=home') ?>"
                               style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: var(--color-bg-light); border: 1px solid var(--color-border-card); border-radius: 8px; text-decoration: none; color: inherit; transition: background 0.2s ease, border-color 0.2s ease;"
                               onmouseover="this.style.background='var(--color-bg-badge)'; this.style.borderColor='var(--color-primary)';"
                               onmouseout="this.style.background='var(--color-bg-light)'; this.style.borderColor='var(--color-border-card)';"
                               title="Details anzeigen">
                                <div style="display: flex; flex-direction: column; gap: 4px; max-width: 85%;">
                                    <!-- Zugehörige Gruppe -->
                                    <span style="font-size: 0.75rem; color: var(--color-primary); font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em;">
                                        👥 <?= esc($meet['group_name']) ?>
                                    </span>
                                    <!-- Titel des Flugtreffens -->
                                    <span style="font-weight: 700; font-size: 0.95rem; color: var(--color-text-title); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">
                                        <?= esc($meet['title']) ?>
                                    </span>
                                    <!-- Termin und Spot -->
                                    <span style="font-size: 0.8rem; color: var(--color-text-muted-dark);">
                                        Am <?= date('d.m.Y', strtotime($meet['meet_date'])) ?> um <?= date('H:i', strtotime($meet['meet_time'])) ?> Uhr • 📍 <?= esc($meet['location']) ?>
                                    </span>
                                </div>
                                <i class="ph ph-caret-right" style="font-size: 1.2rem; color: var(--color-text-muted);"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="fm-empty-text" style="margin-top: 8px; line-height: 1.4;">
                        Aktuell gibt es keine neuen Flugtreffen in deinen Gruppen. Tritt weiteren Gruppen bei, um hier auf dem Laufenden zu bleiben.
                    </p>
                <?php endif; ?>
            </div>

            <!-- Kachel 2: Personalisierte Gruppenempfehlungen (Gruppenfinder) -->
            <div class="fm-dashboard-chart-card" style="max-width: 100%; margin-top: 0;">

                <h3 style="display: flex; align-items: center; gap: 8px; margin-top: 0; margin-bottom: 4px;">
                    <i class="ph ph-compass" style="color: var(--color-primary); font-size: 1.3rem;"></i>
                    Gruppen, die zu dir passen könnten
                </h3>

                <p style="margin: 0 0 16px 0; font-size: 0.85rem; color: var(--color-text-muted-dark); line-height: 1.4;">
                    Vorschläge basierend auf deinen ausgewählten Flugregionen in deinem Profil.
                </p>

                <?php if (!empty($recommendedGroups)): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                        <?php foreach ($recommendedGroups as $rg): ?>
                            <div style="background: var(--color-bg-light); border: 1px solid var(--color-border-card); padding: 14px 16px; border-radius: 8px; display: flex; flex-direction: column; justify-content: space-between; gap: 10px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 4px;">
                                        <!-- Name der Gruppe -->
                                        <span style="font-weight: 700; font-size: 1rem; color: var(--color-text-title); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%; display: block;">
                                            <?= esc($rg['name']) ?>
                                        </span>
                                        <!-- Fokusregion -->
                                        <span class="fm-badge-region" style="font-size: 0.7rem; padding: 2px 8px;"><?= esc($rg['region']) ?></span>
                                    </div>
                                    <span style="font-size: 0.8rem; color: var(--color-text-muted-dark);">
                                        Mitgliederanzahl: <strong><?= esc($rg['members_count']) ?> Piloten</strong>
                                    </span>
                                </div>

                                <div style="display: flex; justify-content: flex-end; border-top: 1px dashed var(--color-border-medium); padding-top: 10px; margin-top: 4px;">
                                    <a href="<?= site_url('flightmeet/groups/detail/' . $rg['id']) ?>"
                                       style="color: var(--color-primary); font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                        Gruppe ansehen <i class="ph ph-caret-right" style="font-size: 1rem;"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Fallback: Link zum Ausfüllen der Regionen im Profil -->
                    <p class="fm-empty-text" style="margin-top: 8px; line-height: 1.4;">
                        Wähle in deinem <a href="<?= site_url('flightmeet/profile') ?>" style="color: var(--color-primary); text-decoration: none; font-weight: 700;">Profil</a> deine Flugregionen aus, um hier passende Gruppen-Vorschläge zu erhalten.
                    </p>
                <?php endif; ?>
            </div>

        </div>

        <!-- RECHTE SEITENLEISTE (Actions & Wetter-Widget) -->
        <div style="display: flex; flex-direction: column; gap: 24px;">

            <!-- Kachel 1: Schnellzugriffe (Quick Actions) -->
            <div class="fm-detail-card">
                <h3 class="fm-detail-card-title" style="margin-bottom: 16px;">Schnellzugriff</h3>
                <div class="fm-sidebar-actions" style="margin-top: 0;">
                    <a href="<?= site_url('flightmeet/meetups/create') ?>" class="btn-new-meet" style="justify-content: center; width: 100%; text-decoration: none;">
                        <i class="ph ph-plus"></i> Neues Treffen planen
                    </a>
                    <a href="<?= site_url('flightmeet/meetups') ?>" class="btn-secondary-full" style="text-align: center;">
                        <i class="ph ph-magnifying-glass"></i> Treffen durchsuchen
                    </a>
                    <a href="<?= site_url('flightmeet/groups') ?>" class="btn-secondary-full" style="text-align: center;">
                        <i class="ph ph-users"></i> Zu meinen Gruppen
                    </a>
                </div>
            </div>

            <!-- Kachel 2: Nächstes Flugtreffen mit dynamischen Wind- und Wettervorhersagen -->
            <?php if ($weather !== null && $nextMeetup !== null): ?>
                <a href="<?= site_url('flightmeet/meetups/' . $nextMeetup['id'] . '?from=home') ?>"
                   class="fm-detail-card"
                   style="background: var(--color-bg-card); border-color: var(--color-border-card); text-decoration: none; display: block; color: inherit;">

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="color: var(--color-primary); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                            <img width="16px" style="vertical-align: middle;" src="<?= base_url('assets/icons/paraglider.png') ?>"> Nächstes Flugtreffen
                        </span>
                        <i class="ph ph-arrow-square-out" style="font-size: 1.1rem; color: var(--color-text-muted);"></i>
                    </div>

                    <h3 style="margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 700; color: var(--color-text-title); line-height: 1.3;">
                        <?= esc($nextMeetup['title']) ?>
                    </h3>

                    <!-- Termindetails -->
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.85rem; color: var(--color-text-muted-dark); margin-bottom: 14px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="ph ph-calendar-blank" style="color: var(--color-primary); font-size: 1.1rem;"></i>
                            <span><?= esc($nextMeetup['date']) ?> um <?= esc($nextMeetup['time']) ?> Uhr</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="ph ph-map-pin" style="color: var(--color-primary); font-size: 1.1rem;"></i>
                            <span style="font-weight: 500; color: var(--color-text-dark);"><?= esc($nextMeetup['location']) ?></span>
                        </div>
                    </div>

                    <!-- Stundengenaue Wetterdaten für die Startzeit -->
                    <div style="border-top: 1px dashed var(--color-border-medium); padding-top: 12px;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--color-primary); letter-spacing: 0.05em; display: flex; align-items: center; gap: 4px; margin-bottom: 8px;">
                            <i class="ph ph-clock" style="font-size: 0.95rem;"></i> Vorhersage für <?= esc($nextMeetup['time']) ?> Uhr
                        </span>

                        <!-- Wetterkurzinfo -->
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            <i class="ph <?= esc($weather['icon']) ?>" style="font-size: 2.2rem; color: var(--color-primary);"></i>
                            <div>
                                <span style="font-size: 1.4rem; font-weight: 700; color: var(--color-text-title);"><?= esc($weather['temp']) ?>°C</span>
                                <span style="display: block; font-size: 0.85rem; color: var(--color-text-muted-dark);"><?= esc($weather['desc']) ?></span>
                            </div>
                        </div>

                        <!-- Wind- und Flugbedingungen -->
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--color-text-muted-dark); margin-bottom: 4px;">
                            <span>Windstärke:</span>
                            <strong style="color: var(--color-text-title);"><?= esc($weather['wind']) ?> km/h</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--color-text-muted-dark);">
                            <span>Windrichtung:</span>
                            <strong style="color: var(--color-text-title);"><?= esc($weather['wind_dir']) ?></strong>
                        </div>
                    </div>
                </a>
            <?php else: ?>
                <!-- Statische Kachel als Hinweiserklärung, falls keine Flüge anstehen -->
                <div class="fm-detail-card" style="background: var(--color-bg-card); border-color: var(--color-border-card);">
                    <h4 style="margin: 0 0 12px 0; color: var(--color-text-title); font-weight: 700; display: flex; align-items: center; gap: 8px;">
                        <i class="ph ph-cloud-sun" style="font-size: 1.3rem; color: var(--color-primary);"></i>
                        Wetter am nächsten Spot
                    </h4>
                    <p style="margin: 0; font-size: 0.9rem; color: var(--color-text-muted-light); line-height: 1.4; font-style: italic;">
                        Keine anstehenden Flugtreffen gefunden. Tritt einem geplanten Treffen bei, um das Wetter für den Startplatz zu laden.
                    </p>
                </div>
            <?php endif; ?>

        </div>

    </div>

<?= $this->endSection() ?>