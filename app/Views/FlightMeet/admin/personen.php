<?= $this->extend('FlightMeet/layout') ?>

<?= $this->section('content') ?>

    <div class="fm-detail-layout" style="display: block; max-width: 1200px; margin: 0 auto; padding: 20px;">

        <!-- Header-Bereich -->
        <div class="fm-detail-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 class="fm-detail-title" style="margin: 0 0 4px 0; display: flex; align-items: center; gap: 10px;">
                    <i class="ph ph-users-three" style="color: var(--color-primary);"></i>
                    Mitgliederverwaltung (REST API)
                </h1>
                <p class="lead" style="margin: 0;">Verwalte die registrierten Piloten und Berechtigungsstufen von FlightMeet.</p>
            </div>

            <!-- Live-Suche -->
            <div style="position: relative;">
                <input type="text" id="user-search" placeholder="Mitglieder durchsuchen..."
                       style="padding: 10px 16px; border-radius: 8px; border: 1px solid var(--color-border-card); background: var(--color-bg-card); color: var(--color-text-title); width: 260px; font-size: 0.9rem;">
            </div>
        </div>

        <!-- API Feedback Status-Banner -->
        <div id="api-feedback" style="display: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 0.9rem;"></div>

        <!-- Mitglieder-Tabelle-Card -->
        <div class="fm-dashboard-chart-card" style="max-width: 100%; margin-top: 0; padding: 24px;">

            <!-- Lade-Spinner -->
            <div id="loading-spinner" style="text-align: center; padding: 40px 0; color: var(--color-text-muted);">
                <i class="ph ph-spinner-gap" style="font-size: 2rem; display: inline-block; animation: spin 1s linear infinite;"></i>
                <p style="margin-top: 8px; font-size: 0.95rem;">Lade Mitgliederliste...</p>
            </div>

            <!-- Die Datentabelle -->
            <div id="table-container" style="display: none; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                    <tr style="border-bottom: 2px solid var(--color-border-medium); color: var(--color-text-title); font-weight: 700;">
                        <th style="padding: 12px 8px;">Username</th>
                        <th style="padding: 12px 8px;">E-Mail</th>
                        <th style="padding: 12px 8px;">Erfahrungslevel</th>
                        <th style="padding: 12px 8px; text-align: center;">Rolle</th>
                        <th style="padding: 12px 8px; text-align: right;">Aktionen</th>
                    </tr>
                    </thead>
                    <tbody id="users-tbody">
                    <!-- Wird dynamisch befüllt -->
                    </tbody>
                </table>
            </div>

            <p id="empty-search-text" class="fm-empty-text" style="display: none; margin-top: 20px;">Keine Mitglieder mit diesem Suchbegriff gefunden.</p>
        </div>
    </div>

    <!-- ==================== EDIT MODAL (OVERLAY) ==================== -->
    <div id="edit-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: var(--color-bg-card); border: 1px solid var(--color-border-card); border-radius: 12px; width: min(450px, 90%); padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.25);">
            <h3 style="margin-top:0; margin-bottom: 18px; color: var(--color-text-title); display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-user-gear" style="color: var(--color-primary); font-size: 1.4rem;"></i>
                Mitglied bearbeiten
            </h3>

            <form id="edit-user-form" style="display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" id="edit-user-id">

                <label style="display: flex; flex-direction: column; gap: 6px;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-muted-dark);">Benutzername</span>
                    <input type="text" id="edit-username" required
                           style="padding: 10px; border-radius: 6px; border: 1px solid var(--color-border-card); background: var(--color-bg-light); color: var(--color-text-title); font-size: 0.9rem;">
                </label>

                <label style="display: flex; flex-direction: column; gap: 6px;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-muted-dark);">Erfahrungslevel</span>
                    <select id="edit-level" style="padding: 10px; border-radius: 6px; border: 1px solid var(--color-border-card); background: var(--color-bg-light); color: var(--color-text-title); font-size: 0.9rem;">
                        <option value="Einsteiger">Einsteiger</option>
                        <option value="Fortgeschritten">Fortgeschritten</option>
                        <option value="Profi">Profi</option>
                    </select>
                </label>

                <label style="display: flex; flex-direction: column; gap: 6px;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-muted-dark);">Rolle</span>
                    <select id="edit-role" style="padding: 10px; border-radius: 6px; border: 1px solid var(--color-border-card); background: var(--color-bg-light); color: var(--color-text-title); font-size: 0.9rem;">
                        <option value="user">USER</option>
                        <option value="admin">ADMIN</option>
                    </select>
                </label>

                <!-- Buttons im Modal -->
                <div style="display: flex; gap: 10px; margin-top: 14px; justify-content: flex-end;">
                    <!-- NEU: id="btn-close-modal" hinzugefügt, inline-onclick entfernt -->
                    <button type="button" id="btn-close-modal" class="btn-secondary" style="padding: 8px 16px; font-size: 0.85rem; cursor: pointer; border-radius: 6px;">Abbrechen</button>
                    <button type="submit" class="btn" style="padding: 8px 16px; font-size: 0.85rem; cursor: pointer; border-radius: 6px; border:none; background: var(--color-primary); color: white; font-weight: 600;">Speichern</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Vollständige Kapselung der API-Verbindungsschlüssel im lokalen JS-Scope
            const apiToken = '<?= $apiToken ?? "" ?>';
            const currentUserId = <?= (int)session()->get('fm_user_id') ?>;
            const apiBaseUrl = '<?= base_url('api/flightmeet/admin/users') ?>';

            const loadingSpinner = document.getElementById('loading-spinner');
            const tableContainer = document.getElementById('table-container');
            const tbody = document.getElementById('users-tbody');
            const searchInput = document.getElementById('user-search');
            const emptyText = document.getElementById('empty-search-text');
            const feedbackEl = document.getElementById('api-feedback');

            // Modal-Elemente
            const editModal = document.getElementById('edit-modal');
            const editForm = document.getElementById('edit-user-form');
            const closeModalBtn = document.getElementById('btn-close-modal');
            const editUserIdInput = document.getElementById('edit-user-id');
            const editUsernameInput = document.getElementById('edit-username');
            const editLevelInput = document.getElementById('edit-level');
            const editRoleInput = document.getElementById('edit-role');

            let allUsers = [];

            // 1. GET: Alle Mitglieder laden
            function loadMembers() {
                fetch(apiBaseUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${apiToken}`,
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        if (!response.ok) throw new Error('API-Zugriff fehlgeschlagen oder nicht autorisiert.');
                        return response.json();
                    })
                    .then(data => {
                        allUsers = data;
                        renderTable(allUsers);
                        loadingSpinner.style.display = 'none';
                        tableContainer.style.display = 'block';
                    })
                    .catch(err => {
                        loadingSpinner.style.display = 'none';
                        showFeedback(err.message, 'error');
                    });
            }

            // 2. DOM-Tabelle rendern (XSS-sicher strukturiert, frei von inline onclicks)
            function renderTable(usersList) {
                tbody.innerHTML = '';

                if (usersList.length === 0) {
                    emptyText.style.display = 'block';
                    tableContainer.style.display = 'none';
                    return;
                }

                emptyText.style.display = 'none';
                tableContainer.style.display = 'block';

                usersList.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.id = `user-row-${user.id}`;
                    tr.style.borderBottom = '1px solid var(--color-border-card)';
                    tr.style.transition = 'background 0.2s';

                    const isSelf = parseInt(user.id) === currentUserId;

                    tr.innerHTML = `
                <td id="td-username-${user.id}" style="padding: 12px 8px; font-weight: 600; color: var(--color-text-title);">
                    ${escapeHtml(user.username)} ${isSelf ? '<span style="font-size: 0.75rem; color: var(--color-primary); font-weight: normal;">(Du)</span>' : ''}
                </td>
                <td style="padding: 12px 8px; color: var(--color-text-muted-dark);">${escapeHtml(user.email)}</td>
                <td id="td-level-${user.id}" style="padding: 12px 8px;">
                    <span class="fm-badge-level fm-badge-level--${user.experience_level.toLowerCase()}" style="font-size: 0.75rem;">
                        ${escapeHtml(user.experience_level)}
                    </span>
                </td>
                <td style="padding: 12px 8px; text-align: center;">
                    <span id="role-badge-${user.id}" class="fm-status fm-status--${user.role === 'admin' ? 'geplant' : 'aktiv'}" style="font-size: 0.75rem; font-weight: 700;">
                        ${user.role.toUpperCase()}
                    </span>
                </td>
                <td style="padding: 12px 8px; text-align: right;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <!-- Bearbeiten (Sicher mit data-id deklariert) -->
                        <button class="btn-action-edit btn-trigger-edit" data-id="${user.id}" title="Profil bearbeiten">
                            <i class="ph ph-pencil" style="font-size: 1.1rem;"></i>
                        </button>
                        <!-- Löschen (Sicher mit data-id und data-username deklariert) -->
                        <button class="btn-action-delete btn-trigger-delete" data-id="${user.id}" data-username="${escapeHtml(user.username)}" title="Dauerhaft löschen" ${isSelf ? 'disabled style="opacity: 0.4; cursor: not-allowed;"' : ''}>
                            <i class="ph ph-trash" style="font-size: 1.1rem;"></i>
                        </button>
                    </div>
                </td>
            `;
                    tbody.appendChild(tr);
                });
            }

            // 3. Modal-Interaktionen (Kapselung im Event-Scope)
            function openEditModal(userId) {
                const user = allUsers.find(u => parseInt(u.id) === userId);
                if (!user) return;

                editUserIdInput.value = user.id;
                editUsernameInput.value = user.username;
                editLevelInput.value = user.experience_level;
                editRoleInput.value = user.role;

                if (userId === currentUserId) {
                    editRoleInput.disabled = true;
                } else {
                    editRoleInput.disabled = false;
                }

                editModal.style.display = 'flex';
            }

            function closeEditModal() {
                editModal.style.display = 'none';
                editForm.reset();
            }

            // Event-Listener für Modal-Abbrechen-Aktion zuweisen
            closeModalBtn.addEventListener('click', closeEditModal);

            // Zentraler Klick-Listener (Event Delegation) für Tabellenaktionen
            tbody.addEventListener('click', (e) => {
                const editBtn = e.target.closest('.btn-trigger-edit');
                if (editBtn) {
                    const userId = parseInt(editBtn.dataset.id);
                    openEditModal(userId);
                    return;
                }

                const deleteBtn = e.target.closest('.btn-trigger-delete');
                if (deleteBtn && !deleteBtn.disabled) {
                    const userId = parseInt(deleteBtn.dataset.id);
                    const username = deleteBtn.dataset.username;
                    deleteUser(userId, username);
                }
            });

            // 4. PUT: Änderungen über REST API abspeichern
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();

                const userId = parseInt(editUserIdInput.value);
                const updatedData = {
                    username: editUsernameInput.value.trim(),
                    experience_level: editLevelInput.value,
                    role: editRoleInput.value
                };

                fetch(`${apiBaseUrl}/${userId}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${apiToken}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(updatedData)
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.messages?.error || err.error || 'Änderung fehlgeschlagen'); });
                        }
                        return response.json();
                    })
                    .then(res => {
                        if (res.success) {
                            // Lokalen Cache aktualisieren
                            const userIndex = allUsers.findIndex(u => parseInt(u.id) === userId);
                            if (userIndex !== -1) {
                                allUsers[userIndex].username = res.user.username;
                                allUsers[userIndex].experience_level = res.user.experience_level;
                                allUsers[userIndex].role = res.user.role;
                            }

                            // DOM-Zeile visuell aktualisieren
                            const isSelf = userId === currentUserId;
                            document.getElementById(`td-username-${userId}`).innerHTML = `${escapeHtml(res.user.username)} ${isSelf ? '<span style="font-size: 0.75rem; color: var(--color-primary); font-weight: normal;">(Du)</span>' : ''}`;
                            document.getElementById(`td-level-${userId}`).innerHTML = `
                    <span class="fm-badge-level fm-badge-level--${res.user.experience_level.toLowerCase()}" style="font-size: 0.75rem;">
                        ${escapeHtml(res.user.experience_level)}
                    </span>`;

                            const roleBadge = document.getElementById(`role-badge-${userId}`);
                            roleBadge.className = `fm-status fm-status--${res.user.role === 'admin' ? 'geplant' : 'aktiv'}`;
                            roleBadge.innerText = res.user.role.toUpperCase();

                            closeEditModal();
                            showFeedback(`Änderungen für "${res.user.username}" wurden erfolgreich gespeichert.`, 'success');
                        }
                    })
                    .catch(err => {
                        showFeedback(err.message, 'error');
                    });
            });

            // 5. DELETE: Account über REST API löschen
            function deleteUser(userId, username) {
                if (!confirm(`Möchtest du den Piloten "${username}" wirklich unwiderruflich löschen?`)) {
                    return;
                }

                fetch(`${apiBaseUrl}/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${apiToken}`,
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Benutzer konnte nicht gelöscht werden.');
                        return response.json();
                    })
                    .then(res => {
                        if (res.success) {
                            allUsers = allUsers.filter(u => parseInt(u.id) !== userId);

                            const row = document.getElementById(`user-row-${userId}`);
                            if (row) {
                                row.style.opacity = '0';
                                row.style.transform = 'scale(0.95)';
                                setTimeout(() => row.remove(), 250);
                            }

                            showFeedback(`Der Pilot "${username}" wurde dauerhaft gelöscht.`, 'success');
                        }
                    })
                    .catch(err => showFeedback(err.message, 'error'));
            }

            // Live-Suche über Event-Listener
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                const filtered = allUsers.filter(u =>
                    u.username.toLowerCase().includes(query) ||
                    u.email.toLowerCase().includes(query)
                );
                renderTable(filtered);
            });

            // Status-Feedback ausgeben (Erfolg/Fehler)
            function showFeedback(msg, type) {
                feedbackEl.innerText = msg;
                feedbackEl.style.display = 'block';
                if (type === 'success') {
                    feedbackEl.style.background = 'rgba(52, 211, 153, 0.15)';
                    feedbackEl.style.border = '1px solid rgba(52, 211, 153, 0.3)';
                    feedbackEl.style.color = '#10b981';
                } else {
                    feedbackEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    feedbackEl.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                    feedbackEl.style.color = '#ef4444';
                }
                setTimeout(() => {
                    feedbackEl.style.display = 'none';
                }, 5000);
            }

            // HTML Escaping Funktion gegen XSS-Angriffe
            function escapeHtml(str) {
                const div = document.createElement('div');
                div.innerText = str;
                return div.innerHTML;
            }

            loadMembers();
        });
    </script>

<?= $this->endSection() ?>