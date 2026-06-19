// Scope isolieren, um globale Namenskonflikte zu vermeiden
document.addEventListener('DOMContentLoaded', () => {

	// HILFSFUNKTION: Mobile-Abfrage zentralisiert
	const isMobileQuery = window.matchMedia('(max-width: 768px)');
	const isMobile = () => isMobileQuery.matches;

	// 1. NAVIGATION & MENÜ-TOGGLE (MOBILE)
	const menuToggle = document.getElementById('fmMenuToggle');
	const menu = document.getElementById('fmMenu');

	if (menuToggle && menu) {
		menuToggle.addEventListener('click', () => {
			const isOpen = menu.classList.toggle('is-open');
			menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});
	}

	// 2. ANSICHT-UMSCHALTER (KARTEN VS. TABELLE)
	const viewButtons = document.querySelectorAll('.fm-toggle-btn');
	const cardsView = document.getElementById('fmCardsView');
	const tableView = document.getElementById('fmTableView');
	const VIEW_STORAGE_KEY = 'flightmeet_view';

	const setActiveView = (view) => {
		if (!cardsView || !tableView) return;

		const isTable = view === 'table';
		cardsView.classList.toggle('fm-view-hidden', isTable);
		tableView.classList.toggle('fm-view-hidden', !isTable);

		viewButtons.forEach((button) => {
			const isActive = button.dataset.view === view;
			button.classList.toggle('is-active', isActive);
			button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
		});
	};

	const storedView = localStorage.getItem(VIEW_STORAGE_KEY) || 'cards';
	const initialView = isMobile() ? 'cards' : storedView;
	setActiveView(initialView);

	viewButtons.forEach((button) => {
		button.addEventListener('click', () => {
			const view = button.dataset.view || 'cards';
			const nextView = isMobile() ? 'cards' : view;
			localStorage.setItem(VIEW_STORAGE_KEY, nextView);
			setActiveView(nextView);
		});
	});

	if (isMobile()) {
		const cardsBtn = document.querySelector('.fm-toggle-btn[data-view="cards"]');
		if (cardsBtn) cardsBtn.click();
	}

	// 3. AUTO-SUBMIT FÜR FILTER-CHECKBOXEN
	document.querySelectorAll('input[data-auto-submit="status"]').forEach((checkbox) => {
		checkbox.addEventListener('change', () => {
			const form = checkbox.closest('form');
			if (form) {
				form.requestSubmit();
			}
		});
	});

	// 4. MAP-PICKER LOGIK (NUR AKTIV BEIM ERSTELLEN/BEARBEITEN)
	// Sorgt dafür, dass man die Karte nutzen kann, um Koordinaten zu setzen
	// Wird eine Region	festgelegt, springt die Karte automatisch dorthin
	const mapPickerEl = document.getElementById('map-picker');

	if (mapPickerEl && typeof L !== 'undefined') {
		const defaultLat = 49.7491;
		const defaultLng = 6.6751;
		const defaultZoom = 9;

		const latInput = document.getElementById('latitude');
		const lngInput = document.getElementById('longitude');

		const oldLat = latInput ? latInput.value : '';
		const oldLng = lngInput ? lngInput.value : '';

		const initialLat = oldLat ? parseFloat(oldLat) : defaultLat;
		const initialLng = oldLng ? parseFloat(oldLng) : defaultLng;
		const hasInitialMarker = (oldLat !== '' && oldLng !== '');

		const mapPicker = L.map('map-picker').setView(
			[initialLat, initialLng],
			hasInitialMarker ? 13 : defaultZoom
		);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; OpenStreetMap-Mitwirkende'
		}).addTo(mapPicker);

		const iconUrl = mapPickerEl.dataset.iconUrl;
		let markerOptions = {};

		if (iconUrl) {
			markerOptions.icon = L.icon({
				iconUrl: iconUrl,
				iconSize: [40, 40],
				iconAnchor: [20, 40],
				popupAnchor: [0, -40]
			});
		}

		let marker = null;

		if (hasInitialMarker) {
			marker = L.marker([initialLat, initialLng], markerOptions).addTo(mapPicker);
			updateIndicator(initialLat, initialLng);
		}

		mapPicker.on('click', function(e) {
			const lat = e.latlng.lat;
			const lng = e.latlng.lng;

			if (marker === null) {
				marker = L.marker([lat, lng], markerOptions).addTo(mapPicker);
			} else {
				marker.setLatLng([lat, lng]);
			}

			if (latInput) latInput.value = lat.toFixed(6);
			if (lngInput) lngInput.value = lng.toFixed(6);

			updateIndicator(lat, lng);
		});

		const regionInput = document.getElementById('region');

		// Wenn eine Region ausgewählt wird, springt die Karte automatisch dorthin
		if (regionInput) {
			regionInput.addEventListener('change', function() {
				const query = this.value.trim();
				if (query.length < 3) return;

				fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
					.then(response => response.json())
					.then(data => {
						if (data && data.length > 0) {
							const lat = parseFloat(data[0].lat);
							const lng = parseFloat(data[0].lon);

							mapPicker.flyTo([lat, lng], 11, { duration: 1.5 });
						}
					})
					.catch(err => console.error("Fehler bei der Ortssuche:", err));
			});
		}

		function updateIndicator(lat, lng) {
			const indicator = document.getElementById('coords-indicator');
			if (indicator) {
				indicator.innerHTML = `📍 Markierter Startplatz: <strong>${lat.toFixed(4)}, ${lng.toFixed(4)}</strong>`;
				indicator.style.color = 'var(--color-primary)';
				indicator.style.fontWeight = '500';
			}
		}
	}

	// 5. ASYNCHRONES CHATSYSTEM (POLLING, SENDEN, DATUM-FNS)

	const messagesArea = document.getElementById('chat-messages-area');
	const messagesContainer = document.getElementById('messages-container');
	const loadMoreBtn = document.getElementById('load-more-btn');
	const chatForm = document.getElementById('chat-submit-form');
	const chatInput = document.getElementById('chat-text-input');
	const chatHeaderTitle = document.getElementById('chat-header-title');

	if (messagesContainer) {
		let currentType = 'global';
		let currentTargetId = null;
		let currentOffset = 0;
		let pollingTimeout = null;
		let isInitialLoad = true;
		const BASE_CHAT_URL = 'chat';

		// Beim Anklicken eines Chats in der Chatliste wird der entsprechende Chat geöffnet, die Oberfläche aktualisiert und die Nachrichten geladen werden.
		document.querySelectorAll('.fm-chat-item').forEach((item) => {
			item.addEventListener('click', () => {
				document.querySelectorAll('.fm-chat-item').forEach(el => el.classList.remove('is-active'));
				item.classList.add('is-active');

				currentType = item.dataset.chatType;
				currentTargetId = item.dataset.targetId ? parseInt(item.dataset.targetId) : null;
				currentOffset = 0;
				isInitialLoad = true;

				//	Chat-Header aktualisieren
				if (chatHeaderTitle) {
					if (currentType === 'global') {
						chatHeaderTitle.innerText = "Globaler Chat";
					} else if (currentType === 'dm') {
						chatHeaderTitle.innerText = `Direktnachricht mit ${item.dataset.username}`;
					} else {
						chatHeaderTitle.innerText = item.querySelector('span')?.innerText || '';
					}
				}

				// UI-Bereinigen, damit	die neuen Nachrichten geladen werden können
				messagesContainer.innerHTML = '';
				loadMoreBtn.style.display = 'none';

				const chatContainer = document.querySelector('.fm-chat-container');
				if (chatContainer) {
					chatContainer.classList.add('show-chat-window');
				}

				//	Nachrichten laden und Nachrichten-Polling starten
				loadMessages(false);
				restartPolling();
			});
		});

		// Back Button an kleinen Geräten, um zur Übersicht zu gelangen
		const backBtn = document.getElementById('chat-back-btn');
		if (backBtn) {
			backBtn.addEventListener('click', () => {
				const chatContainer = document.querySelector('.fm-chat-container');
				if (chatContainer) {
					chatContainer.classList.remove('show-chat-window');
				}
			});
		}

		const triggerDM = document.getElementById('trigger-active-dm');
		if (triggerDM) {
			triggerDM.click();
		} else {
			loadMessages(false);
			restartPolling();
		}

		// Nachrichten laden mit Hilfe von AJAX (Chatcontroller)
		function loadMessages(isLoadMore = false) {
			const offset = isLoadMore ? currentOffset : 0;
			const url = `${BASE_CHAT_URL}/getMessages?type=${currentType}&target_id=${currentTargetId || ''}&offset=${offset}`;

			return fetch(url)
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						// Merken des Zustands vor dem Rendern
						const wasInitial = isInitialLoad;

						renderMessages(data.messages, data.userId, isLoadMore);

						if (isLoadMore) {
							// Fall 1: Benutzer lädt aktiv ältere Nachrichten
							if (data.messages.length < 50) {
								// Keine weiteren Nachrichten mehr vorhanden
								loadMoreBtn.style.display = 'none';
							}
						} else if (wasInitial) {
							// Fall 2: Nur beim ALLERERSTEN Laden des Kanals steuern wir den Button
							if (data.messages.length >= 50) {
								// Es sind weitere Nachrichten vorhanden sein, also Button anzeigen
								loadMoreBtn.style.display = 'block';
							} else {
								// Es sind keine weiteren Nachrichten vorhanden, also Button verstecken
								loadMoreBtn.style.display = 'none';
							}
							isInitialLoad = false; // Initialer Ladevorgang ist hiermit abgeschlossen
						}

						// Fehlerbehandlung
					} else if (data.error) {
						messagesContainer.innerHTML = '';
						const errorPara = document.createElement('p');
						errorPara.className = 'fm-empty';
						errorPara.style.textAlign = 'center';
						errorPara.style.color = 'red';
						errorPara.style.marginTop = '20px';
						errorPara.textContent = data.error;
						messagesContainer.appendChild(errorPara);

						loadMoreBtn.style.display = 'none';
					}
				})
				.catch(err => console.error("Fehler beim Abrufen der Nachrichten:", err));
		}

		// Nachrichten rendern im Chatfenster
		function renderMessages(messages, currentUserId, isLoadMore) {
			const previousScrollHeight = messagesArea.scrollHeight;

			// keine Nachrichten vorhanden
			if (!isLoadMore && messages.length === 0) {
				messagesContainer.innerHTML = `
            <div class="fm-chat-empty-state">
                <i class="ph ph-chat-circle" style="font-size: 3rem;"></i>
                <p>Schreibe die erste Nachricht...</p>
            </div>`;
				return;
			}

			// Wenn beim "Ältere laden" keine Nachrichten mehr kommen, tun wir nichts weiter
			if (isLoadMore && messages.length === 0) {
				return;
			}

			const fragment = document.createDocumentFragment();
			let newMessagesCount = 0;

			messages.forEach((msg) => {
				if (document.getElementById(`msg-${msg.id}`)) return; // Nachricht existiert bereits, überspringen

				newMessagesCount++;
				const wrapper = document.createElement('div');
				const isMe = parseInt(msg.sender_id) === parseInt(currentUserId);
				wrapper.id = `msg-${msg.id}`;
				wrapper.className = `fm-msg-bubble-wrapper ${isMe ? 'is-me' : 'is-other'}`; // Unterscheidung zwischen eigenen und fremden Nachrichten

				let formattedTime = "";
				// Zeitstempel formatieren mit date-fns (z.B. "vor 5 Minuten", "vor etwa 14 Stunden")
				try {
					formattedTime = dateFns.formatDistanceToNow(new Date(msg.created_at), {
						addSuffix: true,
						locale: dateFns.locale.de
					});
				} catch (e) {
					formattedTime = msg.created_at;
				}

				const metaText = isMe ? formattedTime : `${msg.sender_name} • ${formattedTime}`;

				// SICHERE DOM-ERSTELLUNG (Kein innerHTML für unbereinigten Text des Users!) [1]
				const metaSpan = document.createElement('span');
				metaSpan.className = 'fm-msg-meta';
				metaSpan.textContent = metaText;

				// Nachrichtentext
				const bubbleDiv = document.createElement('div');
				bubbleDiv.className = 'fm-msg-bubble';
				bubbleDiv.textContent = msg.message_text;

				wrapper.appendChild(metaSpan);
				wrapper.appendChild(bubbleDiv);
				fragment.appendChild(wrapper);
			});

			// Keine neuen Nachrichten, also nichts weiter tun
			if (newMessagesCount === 0) {
				return;
			}

			if (isLoadMore) {
				// Ältere Nachrichten werden oben angehängt, daher vor den bestehenden Nachrichten einfügen
				messagesContainer.insertBefore(fragment, messagesContainer.firstChild);
				messagesArea.scrollTop = messagesArea.scrollHeight - previousScrollHeight; // Scrollposition beibehalten
			} else {
				// normales Laden von Nachrichten
				const isInitialLoad = messagesContainer.children.length === 0 || messagesContainer.querySelector('.fm-chat-empty-state');
				const isNearBottom = messagesArea.scrollHeight - messagesArea.scrollTop - messagesArea.clientHeight < 150;

				const emptyState = messagesContainer.querySelector('.fm-chat-empty-state');
				if (emptyState) {
					emptyState.remove();
				}

				// Neue Nachrichten werden unten angehängt
				messagesContainer.appendChild(fragment);

				if (isInitialLoad || isNearBottom) {
					scrollChatToBottom();
				}
			}
		}

		loadMoreBtn.addEventListener('click', () => {
			currentOffset += 50;
			loadMessages(true);
		});

		// Nutzer schickt eine Nachricht ab
		chatForm.addEventListener('submit', async (e) => {
			e.preventDefault();

			const text = chatInput.value.trim();

			if (!text) {
				return;
			}

			// Button gegen Doppelklick schützen
			const submitBtn = chatForm.querySelector('button[type="submit"]');

			try {

				if (submitBtn) {
					submitBtn.disabled = true;
				}

				const formData = new FormData();
				formData.append('type', currentType);

				if (currentTargetId !== null) {
					formData.append('target_id', currentTargetId);
				}

				formData.append('message_text', text);

				// Optional: CSRF-Token mitsenden
				// formData.append(csrfName, csrfHash);

				const response = await fetch(`${BASE_CHAT_URL}/sendMessage`, {
					method: 'POST',
					body: formData,
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					}
				});

				// HTTP-Fehler erkennen
				if (!response.ok) {
					throw new Error(`HTTP ${response.status}`);
				}

				const data = await response.json();

				if (!data.success) {
					throw new Error(data.error || 'Nachricht konnte nicht gesendet werden.');
				}

				// Erst jetzt Eingabefeld leeren
				chatInput.value = '';

				// Fokus zurück ins Eingabefeld
				chatInput.focus();

				// Chat aktualisieren
				await loadMessages(false);

			} catch (error) {

				console.error('Senden fehlgeschlagen:', error);

				showError(
					error.message || 'Beim Senden der Nachricht ist ein Fehler aufgetreten.'
				);

			} finally {

				if (submitBtn) {
					submitBtn.disabled = false;
				}
			}
		});

		function scrollChatToBottom() {
			messagesArea.scrollTop = messagesArea.scrollHeight;
		}

		// Verbessertes Polling: Verhindert Überlappungen bei langsamer Verbindung
		function restartPolling() {
			clearTimeout(pollingTimeout);

			const poll = () => {
				loadMessages(false).finally(() => {
					// Erst wenn die Anfrage fertig (erfolgreich/fehlgeschlagen) ist,
					// wird der Timer für die nächste Abfrage gestartet.
					pollingTimeout = setTimeout(poll, 3000);
				});
			};

			pollingTimeout = setTimeout(poll, 3000);
		}
	}

	// 6. DASHBOARD REGIONEN-CHART (CHART.JS)
	const regionChartEl = document.getElementById('regionChart');

	// Prüfen, ob das Canvas-Element existiert und die Bibliothek geladen ist
	if (regionChartEl && typeof Chart !== 'undefined') {
		try {
			// Daten aus dem HTML-Attribut data-stats (regionstats) auslesen und parsen
			const rawData = JSON.parse(regionChartEl.dataset.stats || '[]');

			const regions = rawData.map(item => item.region);
			const counts = rawData.map(item => item.count);

			const ctx = regionChartEl.getContext('2d');
			new Chart(ctx, {
				type: 'bar',
				data: {
					labels: regions,
					datasets: [{
						label: 'Geplante Treffen',
						data: counts,
						backgroundColor: 'rgba(54, 162, 235, 0.15)',
						borderColor: 'rgba(54, 162, 235, 1)',
						borderWidth: 1,
						borderRadius: 6
					}]
				},
				options: {
					responsive: true,
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								stepSize: 1
							}
						}
					},
					plugins: {
						legend: {
							display: false
						}
					}
				}
			});
		} catch (e) {
			console.error("Fehler beim Initialisieren des Regionen-Charts:", e);
		}
	}

	// 7. TABELLEN-SORTIERUNG (LIST.JS)
	const tableViewEl = document.getElementById('fmTableView');
	if (tableViewEl && typeof List !== 'undefined') {
		new List('fmTableView', {
			valueNames: [
				'col-title',
				'col-spot',
				'col-region',
				// Sortiert nach dem Roh-Timestamp im Attribut, damit das Datumsformat die Sortierung nicht stört
				{ name: 'col-date', attr: 'data-timestamp' }
			]
		});
	}

	// 8. DATUMS- & ZEIT-PICKER (FLATPICKR)
	const dateInput = document.getElementById('meet_date');
	const timeInput = document.getElementById('meet_time');

	if (dateInput && typeof flatpickr !== 'undefined') {
		flatpickr(dateInput, {
			locale: 'de',            // Deutsche Sprache aktivieren
			dateFormat: 'Y-m-d',     // Entspricht dem MySQL-DATE-Format (YYYY-MM-DD)
			minDate: 'today',        // Verhindert das Buchen von Terminen in der Vergangenheit
			allowInput: false        // Verhindert manuelle Tastatur-Eingaben
		});
	}

	if (timeInput && typeof flatpickr !== 'undefined') {
		flatpickr(timeInput, {
			locale: 'de',
			enableTime: true,        // Zeit-Auswahl aktivieren
			noCalendar: true,        // Kalender ausblenden (nur Zeit zeigen)
			dateFormat: 'H:i',       // Entspricht dem MySQL-TIME-Format (HH:MM)
			time_24hr: true          // 24-Stunden-Format erzwingen
		});
	}
});