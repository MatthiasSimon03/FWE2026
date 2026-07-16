<?= $this->extend('FlightMeet/layout') ?>
<?= $this->section('content') ?>

    <!-- Laden von date-fns via CDN für die clientseitige Datumsformatierung -->
    <script src="https://cdn.jsdelivr.net/npm/date-fns@3.6.0/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@3.6.0/locale/de/cdn.min.js"></script>

    <div class="fm-chat-container">

        <!-- LINKE SIDEBAR: Kanalauswahl -->
        <div class="fm-chat-sidebar">
            <div class="fm-chat-sidebar-header">Kommunikation</div>

            <div class="fm-chat-channels-list">

                <!-- 1. Globaler Chat -->
                <div class="fm-chat-item is-active" data-chat-type="global" data-target-id="">
                    <i class="ph ph-globe" style="font-size: 1.2rem;"></i>
                    <span>Globaler Chat</span>
                </div>

                <!-- 2. Gruppenkanäle -->
                <div class="fm-chat-section-title">Gruppen</div>
                <?php if (empty($groups)): ?>
                    <p class="fm-empty" style="padding-left: 8px;">Keine Gruppenkanäle aktiv.</p>
                <?php else: ?>
                    <?php foreach ($groups as $g): ?>
                        <div class="fm-chat-item" data-chat-type="group" data-target-id="<?= $g['id'] ?>">
                            <i class="ph ph-hash"></i>
                            <span><?= esc($g['name']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- 3. Aktive Flugtreffen-Chats -->
                <div class="fm-chat-section-title">Flugtreffen (Aktiv)</div>
                <?php if (empty($active_meetups)): ?>
                    <p class="fm-empty" style="padding-left: 8px;">Keine aktiven Chats.</p>
                <?php else: ?>
                    <?php foreach ($active_meetups as $m): ?>
                        <div class="fm-chat-item" data-chat-type="meetup" data-target-id="<?= $m['id'] ?>">
                            <i class="ph ph-airplane"></i>
                            <span><?= esc($m['title']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- 4. Archivierte Flugtreffen-Chats (Historische Treffen) -->
                <?php if (!empty($past_meetups)): ?>
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-muted-light); font-weight: 700; margin: 14px 8px 6px; letter-spacing: 0.05em; list-style: none; display: flex; align-items: center; gap: 4px;">
                            <i class="ph ph-folder-open" style="font-size: 0.9rem;"></i> Archivierte Flugtreffen (<?= count($past_meetups) ?>)
                        </summary>
                        <div style="padding-left: 4px;">
                            <?php foreach ($past_meetups as $m): ?>
                                <div class="fm-chat-item" data-chat-type="meetup" data-target-id="<?= $m['id'] ?>" style="opacity: 0.7;">
                                    <i class="ph ph-archive"></i>
                                    <span><?= esc($m['title']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </details>
                <?php endif; ?>

                <!-- 5. Direktnachrichten (Nach unten verschoben) -->
                <div class="fm-chat-section-title">Piloten (Direkt)</div>
                <?php foreach ($pilots as $p): ?>
                    <!-- Daten-Attribute zur Übergabe an das JS-Chatsystem beim Klick; Aktiv-Trigger bei DM-Direktaufruf -->
                    <div class="fm-chat-item" data-chat-type="dm" data-target-id="<?= $p['id'] ?>" data-username="<?= esc($p['username']) ?>" <?= ($target_user_id === (int)$p['id']) ? 'id="trigger-active-dm"' : '' ?>>
                        <i class="ph ph-user"></i>
                        <span><?= esc($p['username']) ?></span>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- RECHTER HAUPTBEREICH: Das Chatfenster -->
        <div class="fm-chat-window">

            <!-- Chat-Header mit responsivem Zurück-Button für Mobile -->
            <div class="fm-chat-window-header" style="display: flex; align-items: center; gap: 8px;">
                <button class="fm-chat-back-btn" id="chat-back-btn" style="display: none;" title="Zurück zur Auswahl">
                    <i class="ph ph-caret-left" style="font-size: 1.5rem; font-weight: bold;"></i>
                </button>
                <span id="chat-header-title" style="font-weight: 600;">Globaler Chat</span>
            </div>

            <!-- Scrollbarer Nachrichtenverlauf -->
            <div class="fm-chat-messages-scroll" id="chat-messages-area">
                <button class="fm-chat-load-more-btn" id="load-more-btn" style="display:none;">Ältere Nachrichten laden</button>
                <!-- Container für die per Ajax/JS injizierten Nachrichtenblasen -->
                <div id="messages-container" style="display: flex; flex-direction: column; gap: 14px;"></div>
            </div>

            <!-- Eingabebereich für Textnachrichten -->
            <div class="fm-chat-input-area">
                <form class="fm-chat-input-form" id="chat-submit-form" onsubmit="return false;">
                    <input type="text" id="chat-text-input" class="fm-chat-text-input" placeholder="Nachricht schreiben..." autocomplete="off" required>
                    <button type="submit" class="fm-chat-send-btn">
                        <i class="ph ph-paper-plane-right" style="font-size: 1.25rem;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>