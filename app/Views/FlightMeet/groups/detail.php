<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <!-- CSS für den Kalender und das Tab-Layout direkt integriert -->
    <style>
        /* Styling für die Kalenderansicht */
        .fm-calendar-container {
            background: var(--color-bg-white);
            border: 1px solid var(--color-border-card);
            border-radius: 12px;
            padding: 20px;
            width: 100%;
            box-sizing: border-box;
        }
        .fm-calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .fm-calendar-month-btn {
            background: var(--color-bg-badge);
            border: 1px solid var(--color-border-medium);
            padding: 6px 14px;
            border-radius: 999px;
            font-weight: 600;
            cursor: pointer;
            color: var(--color-text-sidebar);
            transition: background 0.15s ease;
        }
        .fm-calendar-month-btn:hover {
            background: var(--color-secondary-hover);
            color: var(--color-text-main);
        }

        /* WICHTIG: minmax(0, 1fr) zwingt die Spalten elastisch zu bleiben, statt zu explodieren */
        .fm-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 8px;
            width: 100%;
        }

        .fm-calendar-weekday {
            text-align: center;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--color-text-muted);
            text-transform: uppercase;
            padding: 5px 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .fm-calendar-day {
            aspect-ratio: 1;
            border: 1px solid var(--color-border-light);
            border-radius: 8px;
            padding: 6px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: var(--color-bg-light);
            min-height: 80px;
            position: relative;
            min-width: 0;      /* Verhindert das Aufblähen durch Flex-Kinder */
            overflow: hidden;  /* Schneidet Überhänge sauber ab */
            box-sizing: border-box;
        }
        .fm-calendar-day.other-month {
            opacity: 0.35;
            background: transparent;
        }
        .fm-calendar-day-num {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--color-text-dark);
            display: block;
        }
        .fm-calendar-day.today {
            border-color: var(--color-primary);
            background: var(--color-primary-light);
        }
        .fm-calendar-day.today .fm-calendar-day-num {
            color: var(--color-primary);
        }
        .fm-calendar-events {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-top: 4px;
            width: 100%;
            min-width: 0;
            overflow: hidden;
        }
        .fm-calendar-event {
            font-size: 0.7rem;
            padding: 2px 4px;
            border-radius: 4px;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis; /* Erzeugt die drei Punkte (...) bei langen Titeln */
            display: block;
            font-weight: 500;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid transparent;
            transition: all 0.15s ease-in-out;
        }

        /* --------------------------------------------------------------------------
           STATUS-FARBEN IM KALENDER
           -------------------------------------------------------------------------- */
        /* 1. Geplant */
        .fm-calendar-event--geplant {
            background-color: var(--color-primary);
            color: #ffffff;
        }
        .fm-calendar-event--geplant:hover {
            background-color: var(--color-primary-hover);
        }

        /* 2. Ausgebucht */
        .fm-calendar-event--ausgebucht {
            background-color: var(--color-status-ausgebucht);
            color: #ffffff;
        }
        .fm-calendar-event--ausgebucht:hover {
            background-color: #92400e;
        }

        /* 3. Abgesagt (Hellrot + Durchgestrichener Text) */
        .fm-calendar-event--abgesagt {
            background-color: var(--color-status-abgesagt-bg);
            color: var(--color-status-abgesagt);
            border-color: var(--color-level-profi-border);
            text-decoration: line-through;
        }
        .fm-calendar-event--abgesagt:hover {
            background-color: #fca5a5;
        }

        /* 4. Abgeschlossen / Vergangenheit (Grau gehalten) */
        .fm-calendar-event--abgeschlossen {
            background-color: var(--color-status-abgeschlossen-bg);
            color: var(--color-text-muted);
            border-color: var(--color-border-card);
        }
        .fm-calendar-event--abgeschlossen:hover {
            background-color: var(--color-border-card);
            color: var(--color-text-main);
        }
    </style>

    <!-- Globale Feedback-Meldungen -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

    <div class="fm-detail-layout">
        <div class="fm-detail-main">
            <div class="fm-detail-header" style="flex-wrap: wrap;">
                <div>
                    <h1 class="fm-detail-title" style="margin: 0;"><?= esc($group['name']) ?></h1>
                    <p style="margin: 4px 0 0 0; color: var(--color-text-muted);">
                        Gegründet von: <strong><?= esc($group['owner_name']) ?></strong>
                    </p>
                </div>

                <!-- Nur der Owner darf die Gruppe bearbeiten oder löschen -->
                <?php if ($user_role === 'owner'): ?>
                    <div class="fm-detail-actions">
                        <a href="<?= base_url('flightmeet/groups/edit/' . $group['id']) ?>" class="btn-action-edit" title="Gruppe bearbeiten">
                            <i class="ph ph-pencil" style="font-size: 1.2rem;"></i>
                        </a>
                        <form method="post" action="<?= base_url('flightmeet/groups/delete/' . $group['id']) ?>" onsubmit="return confirm('Möchten Sie diese Gruppe wirklich löschen? Alle Mitgliedschaften gehen verloren.');" style="display: inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn-action-delete" title="Gruppe löschen">
                                <i class="ph ph-trash" style="font-size: 1.2rem;"></i>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <p class="lead fm-detail-desc"><?= esc($group['description']) ?></p>

            <?php if (!empty($group['rules'])): ?>
                <div class="card">
                    <h3 style="margin-top: 0; font-size: 1rem; color: var(--color-text-title); display: flex; align-items: center; gap: 6px;">
                        <i class="ph ph-scroll" style="font-size: 1.2rem;"></i> Gruppenregeln
                    </h3>
                    <p style="font-size: 0.95rem; line-height: 1.5; white-space: pre-line; margin: 0;"><?= esc($group['rules']) ?></p>
                </div>
            <?php endif; ?>

            <!-- Haupt-Navigation für Gruppeninhalte -->
            <h2 class="fm-section-title">Gruppen-Aktivitäten</h2>
            <div class="fm-view-toggle" style="margin-bottom: 20px; display: inline-flex;">
                <button class="fm-toggle-btn is-active" type="button" data-tab-target="tab-list">
                    <i class="ph ph-list-bullets" style="vertical-align: middle;"></i> Flugliste
                </button>
                <button class="fm-toggle-btn" type="button" data-tab-target="tab-map">
                    <i class="ph ph-map-trifold" style="vertical-align: middle;"></i> Gruppenkarte
                </button>
                <button class="fm-toggle-btn" type="button" data-tab-target="tab-calendar">
                    <i class="ph ph-calendar" style="vertical-align: middle;"></i> Kalender
                </button>
            </div>

            <!-- TAB 1: FLUGLISTE -->
            <div id="tab-list" class="tab-content">
                <div class="fm-view-toggle" style="margin-bottom: 15px; background-color: var(--color-bg-light); padding: 4px;">
                    <button class="fm-toggle-btn is-active" type="button" onclick="switchFlightTab('active')" style="font-size: 0.85rem;">Geplant</button>
                    <button class="fm-toggle-btn" type="button" onclick="switchFlightTab('historic')" style="font-size: 0.85rem;">Vergangene Flüge</button>
                </div>

                <div id="flights-active">
                    <?php if (empty($scheduled_flights)): ?>
                        <p class="fm-empty">Aktuell sind keine geplanten Flüge von Mitgliedern aktiv.</p>
                    <?php else: ?>
                        <div class="fm-grid" style="grid-template-columns: 1fr;">
                            <?php foreach ($scheduled_flights as $flight): ?>
                                <a href="<?= base_url('flightmeet/meetups/' . $flight['id']) ?>?from_group=<?= $group['id'] ?>" class="fm-card fm-card--link" style="padding: 14px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                        <h4 style="margin:0; font-size: 1rem;"><?= esc($flight['title']) ?></h4>
                                        <span class="fm-status fm-status--<?= esc($flight['status']) ?>"><?= esc($flight['status']) ?></span>
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 20px; font-size: 0.85rem; color: var(--color-text-muted); margin-top: 4px; align-items: center;">
                                        <span style="display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-map-pin"></i> <?= esc($flight['location']) ?></span>
                                        <span style="display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-calendar"></i> <?= date('d.m.Y', strtotime($flight['meet_date'])) ?> um <?= date('H:i', strtotime($flight['meet_time'])) ?> Uhr</span>
                                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="ph ph-user"></i>
                                            <?php if ((int)$flight['creator_is_private'] === 1): ?>
                                                <i class="ph ph-lock" style="font-size: 0.9rem;"></i> Privater Ersteller
                                            <?php else: ?>
                                                <?= esc($flight['creator_name']) ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="flights-historic" style="display: none;">
                    <?php if (empty($historic_flights)): ?>
                        <p class="fm-empty">Keine vergangenen Gruppenflüge vorhanden.</p>
                    <?php else: ?>
                        <div class="fm-grid" style="grid-template-columns: 1fr;">
                            <?php foreach ($historic_flights as $flight): ?>
                                <a href="<?= base_url('flightmeet/meetups/' . $flight['id']) ?>?from_group=<?= $group['id'] ?>" class="fm-card fm-card--link" style="padding: 14px; opacity: 0.85;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                        <h4 style="margin:0; font-size: 1rem; color: var(--color-text-muted-dark);"><?= esc($flight['title']) ?></h4>
                                        <span class="fm-status fm-status--<?= esc($flight['status']) ?>"><?= esc($flight['status']) ?></span>
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 20px; font-size: 0.85rem; color: var(--color-text-muted); margin-top: 4px; align-items: center;">
                                        <span style="display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-map-pin"></i> <?= esc($flight['location']) ?></span>
                                        <span style="display: inline-flex; align-items: center; gap: 4px;"><i class="ph ph-calendar"></i> <?= date('d.m.Y', strtotime($flight['meet_date'])) ?></span>
                                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="ph ph-user"></i>
                                            <?php if ((int)$flight['creator_is_private'] === 1): ?>
                                                <i class="ph ph-lock" style="font-size: 0.9rem;"></i> Privater Ersteller
                                            <?php else: ?>
                                                <?= esc($flight['creator_name']) ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 2: GRUPPENKARTE -->
            <div id="tab-map" class="tab-content" style="display: none;">
                <div id="group-flights-map"
                     style="height: 480px; width: 100%; border-radius: 12px; border: 1px solid var(--color-border-medium);"
                     data-base-lat="<?= esc($group['latitude']) ?>"
                     data-base-lng="<?= esc($group['longitude']) ?>"
                     data-base-name="<?= esc($group['name']) ?>"
                     data-flights='<?= json_encode($scheduled_flights) ?>'
                     data-icon-url="<?= base_url('assets/icons/paraglider.png') ?>">
                </div>
                <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 8px; text-align: center;">
                    Blaues Symbol: Gruppen-Stützpunkt | <img width="16px" style="vertical-align: middle;" src="<?= base_url('assets/icons/paraglider.png') ?>"> Geplante Gruppenflüge
                </p>
            </div>

            <!-- TAB 3: KALENDERANSICHT -->
            <div id="tab-calendar" class="tab-content" style="display: none;">
                <div class="fm-calendar-container">
                    <div class="fm-calendar-header">
                        <button class="fm-calendar-month-btn" onclick="prevMonth()">
                            <i class="ph ph-caret-left"></i> Zurück
                        </button>
                        <h3 id="calendar-title" style="margin: 0; color: var(--color-text-title);"></h3>
                        <button class="fm-calendar-month-btn" onclick="nextMonth()">
                            Weiter <i class="ph ph-caret-right"></i>
                        </button>
                    </div>
                    <div class="fm-calendar-grid" id="calendar-grid">
                        <!-- Wochentage & Zellen werden per JS injiziert -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Rechte Sidebar -->
        <div class="fm-detail-sidebar">
            <div class="fm-detail-card">
                <h3 class="fm-detail-card-title">Gruppen-Details</h3>

                <dl class="fm-detail-info-list">
                    <div class="fm-detail-info-item">
                        <dt><i class="ph ph-map-pin icons-meetup-detail"></i></dt>
                        <dd><strong><?= esc($group['base_location'] ?: 'Kein Stützpunkt') ?></strong></dd>
                    </div>
                    <div class="fm-detail-info-item">
                        <dt><i class="ph ph-compass icons-meetup-detail"></i></dt>
                        <dd><span class="fm-badge-region"><?= esc($group['region'] ?: 'Fokusregion fehlt') ?></span></dd>
                    </div>
                    <div class="fm-detail-info-item">
                        <dt><i class="ph ph-users icons-meetup-detail"></i></dt>
                        <dd><span class="fm-status fm-status--geplant"><?= count($members) ?> Piloten</span></dd>
                    </div>
                </dl>

                <div class="fm-sidebar-actions" style="margin-top: 20px;">
                    <?php if ($is_member): ?>
                        <form method="post" action="<?= base_url('flightmeet/groups/leave/' . $group['id']) ?>" style="width: 100%;">
                            <?= csrf_field() ?>
                            <button class="btn-danger-full" type="submit" <?= $user_role === 'owner' ? 'disabled title="Besitzer können nicht austreten"' : '' ?>>Gruppe verlassen</button>
                        </form>
                    <?php else: ?>
                        <?php if ($group['visibility'] === 'open'): ?>
                            <form method="post" action="<?= base_url('flightmeet/groups/join/' . $group['id']) ?>" style="width: 100%;">
                                <?= csrf_field() ?>
                                <button class="btn btn-primary-full" type="submit">Gruppe beitreten</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a class="btn-secondary-full" href="<?= base_url('flightmeet/groups') ?>">Zurück zur Übersicht</a>
                </div>

                <!-- Beitrittsanfragen -->
                <?php if (!empty($pending_requests)): ?>
                    <hr class="fm-divider">
                    <h4 class="fm-sidebar-subtitle">Offene Beitrittsanfragen (<?= count($pending_requests) ?>)</h4>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($pending_requests as $req): ?>
                            <div style="background-color: var(--color-bg-light); border: 1px solid var(--color-border-card); padding: 10px; border-radius: 6px; font-size: 0.85rem;">
                                <strong><?= esc($req['username']) ?></strong> <span class="fm-badge-level fm-badge-level--einsteiger" style="font-size: 0.7rem; padding: 1px 4px;"><?= esc($req['experience_level']) ?></span>
                                <?php if ($req['message']): ?>
                                    <p style="font-style: italic; margin: 4px 0; color: var(--color-text-muted-dark);">"<?= esc($req['message']) ?>"</p>
                                <?php endif; ?>
                                <div style="margin-top: 8px; display: flex; gap: 6px;">
                                    <form method="post" action="<?= base_url('flightmeet/groups/approve-request/' . $req['id']) ?>" style="flex: 1;">
                                        <?= csrf_field() ?>
                                        <button class="btn" style="padding: 4px; font-size: 0.8rem; width: 100%; border: none; cursor: pointer;">Annehmen</button>
                                    </form>
                                    <form method="post" action="<?= base_url('flightmeet/groups/reject-request/' . $req['id']) ?>" style="flex: 1;">
                                        <?= csrf_field() ?>
                                        <button class="btn-danger-full" style="padding: 4px; font-size: 0.8rem; border-radius: 6px;">Ablehnen</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Mitglieder-Verwaltung -->
                <hr class="fm-divider">
                <h4 class="fm-sidebar-subtitle" style="margin-bottom: 12px;">Mitglieder-Verwaltung</h4>
                <ul class="fm-participants-list" style="max-height: 250px;">
                    <?php foreach ($members as $m): ?>
                        <li style="display: flex; flex-direction: column; align-items: stretch; gap: 4px;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-weight: 500; font-size: 0.9rem;">
                                👤 <?= esc($m['username']) ?>
                                <?php if ($m['role'] === 'owner'): ?>
                                    <span style="color: var(--color-status-ausgebucht); font-size: 0.75rem; font-weight: bold;">(Besitzer)</span>
                                <?php elseif ($m['role'] === 'admin'): ?>
                                    <span style="color: var(--color-status-geplant); font-size: 0.75rem; font-weight: bold;">(Admin)</span>
                                <?php endif; ?>
                            </span>
                                <?php if ((int)$m['user_id'] !== (int)session()->get('fm_user_id')): ?>
                                    <a style="text-decoration: none" href="<?= base_url('flightmeet/chat') ?>"><i class="ph ph-envelope icons-meetup-mail"></i></a>
                                <?php endif; ?>
                            </div>

                            <!-- Mitgliederverwaltung -->
                            <?php if (in_array($user_role, ['owner', 'admin'], true) && (int)$m['user_id'] !== (int)session()->get('fm_user_id') && $m['role'] !== 'owner'): ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;">
                                    <?php if ($user_role === 'owner'): ?>
                                        <?php if ($m['role'] === 'member'): ?>
                                            <form method="post" action="<?= base_url('flightmeet/groups/promote/' . $group['id'] . '/' . $m['user_id']) ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn-member-action btn-member-action--promote">Befördern</button>
                                            </form>
                                        <?php elseif ($m['role'] === 'admin'): ?>
                                            <form method="post" action="<?= base_url('flightmeet/groups/demote/' . $group['id'] . '/' . $m['user_id']) ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn-member-action btn-member-action--promote">Degradieren</button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="post" action="<?= base_url('flightmeet/groups/transfer-owner/' . $group['id'] . '/' . $m['user_id']) ?>" onsubmit="return confirm('Möchtest du die Inhaberschaft wirklich an <?= esc($m['username']) ?> übertragen? Du wirst dadurch zum Admin herabgestuft.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-member-action btn-member-action--transfer">Besitz übertragen</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php
                                    $canKick = ($user_role === 'owner') || ($user_role === 'admin' && $m['role'] === 'member');
                                    ?>
                                    <?php if ($canKick): ?>
                                        <form method="post" action="<?= base_url('flightmeet/groups/remove-member/' . $group['id'] . '/' . $m['user_id']) ?>" onsubmit="return confirm('Möchtest du dieses Mitglied wirklich entfernen?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn-member-action btn-member-action--kick">Kicken</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

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

        let gCalendar = null;

        document.addEventListener('DOMContentLoaded', () => {
            // Wartet in sehr kurzen Abständen, bis flightmeet.js geladen und bereit ist
            const checkInterval = setInterval(() => {
                if (typeof initDynamicFlightsMap !== 'undefined' && typeof DynamicFlightCalendar !== 'undefined') {
                    clearInterval(checkInterval); // Intervall stoppen, sobald Funktionen existieren

                    // Generische Karte initialisieren (Basis-Stützpunkt + Flüge)
                    initDynamicFlightsMap('group-flights-map');

                    // Generischen Kalender initialisieren
                    const calendarFlights = <?= json_encode(array_merge($scheduled_flights, $historic_flights)) ?>;
                    gCalendar = new DynamicFlightCalendar('calendar', calendarFlights, '<?= base_url() ?>', '<?= $group['id'] ?>');

                    // Event-Handler für die Buttons zuweisen
                    window.prevMonth = () => { gCalendar.prev(); };
                    window.nextMonth = () => { gCalendar.next(); };

                    gCalendar.render();
                }
            }, 30); // Alle 30ms prüfen
        });
    </script>

<?= $this->endSection() ?>