// Scope isolieren, um globale Namenskonflikte zu vermeiden
document.addEventListener('DOMContentLoaded', () => {

	// HILFSFUNKTION: Mobile-Abfrage zentralisieren (DRY)
	const isMobileQuery = window.matchMedia('(max-width: 768px)');
	const isMobile = () => isMobileQuery.matches;

	// ==========================================================================
	// 1. NAVIGATION & MENÜ-TOGGLE (MOBILE)
	// ==========================================================================
	const menuToggle = document.getElementById('fmMenuToggle');
	const menu = document.getElementById('fmMenu');

	if (menuToggle && menu) {
		menuToggle.addEventListener('click', () => {
			const isOpen = menu.classList.toggle('is-open');
			menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});
	}

	// ==========================================================================
	// 2. ANSICHT-UMSCHALTER (KARTEN VS. TABELLE)
	// ==========================================================================
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

	// ==========================================================================
	// 3. AUTO-SUBMIT FÜR FILTER-CHECKBOXEN
	// ==========================================================================
	document.querySelectorAll('input[data-auto-submit="status"]').forEach((checkbox) => {
		checkbox.addEventListener('change', () => {
			const form = checkbox.closest('form');
			if (form) {
				form.requestSubmit();
			}
		});
	});

	// ==========================================================================
	// 4. MAP-PICKER LOGIK (NUR AKTIV BEIM ERSTELLEN/BEARBEITEN)
	// ==========================================================================
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

		if (regionInput) {
			regionInput.addEventListener('change', function() {
				const query = this.value.trim();
				if (query.length < 3) return;

				// TIPP: Im Produktivbetrieb empfiehlt sich hier ein Debounce, falls auf 'input' gelauscht wird.
				// Da es auf 'change' liegt (Verlassen des Feldes), ist es so in Ordnung.
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

	// ==========================================================================
	// 5. ASYNCHRONES CHATSYSTEM (POLLING, SENDEN, DATUM-FNS)
	// ==========================================================================
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
		let pollingTimeout = null; // Umstellung auf Timeout zur Vermeidung von Polling-Staus

		// HINWEIS: Nutzen Sie im Live-Betrieb am besten absolute Pfade bezogen auf die Domain,
		// z.B. '/flightmeet/chat/getMessages', um Routingfehler durch relative Pfade zu umgehen.
		const BASE_CHAT_URL = 'chat';

		document.querySelectorAll('.fm-chat-item').forEach((item) => {
			item.addEventListener('click', () => {
				document.querySelectorAll('.fm-chat-item').forEach(el => el.classList.remove('is-active'));
				item.classList.add('is-active');

				currentType = item.dataset.chatType;
				currentTargetId = item.dataset.targetId ? parseInt(item.dataset.targetId) : null;
				currentOffset = 0;

				if (chatHeaderTitle) {
					if (currentType === 'global') {
						chatHeaderTitle.innerText = "Globaler Chat";
					} else if (currentType === 'dm') {
						chatHeaderTitle.innerText = `Direktnachricht mit ${item.dataset.username}`;
					} else {
						chatHeaderTitle.innerText = item.querySelector('span')?.innerText || '';
					}
				}

				messagesContainer.innerHTML = '';
				loadMoreBtn.style.display = 'none';

				const chatContainer = document.querySelector('.fm-chat-container');
				if (chatContainer) {
					chatContainer.classList.add('show-chat-window');
				}

				loadMessages(false);
				restartPolling();
			});
		});

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

		// Funktion gibt jetzt das Promise zurück, damit das Polling steuern kann, wann die nächste Abfrage startet
		function loadMessages(isLoadMore = false) {
			const url = `${BASE_CHAT_URL}/getMessages?type=${currentType}&target_id=${currentTargetId || ''}&offset=${currentOffset}`;

			return fetch(url)
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						renderMessages(data.messages, data.userId, isLoadMore);
						if (data.messages.length >= 50 && !isLoadMore) {
							loadMoreBtn.style.display = 'block';
						}
					} else if (data.error) {
						messagesContainer.innerHTML = `<p class="fm-empty" style="text-align:center; color:red; margin-top:20px;">${data.error}</p>`;
						loadMoreBtn.style.display = 'none';
					}
				})
				.catch(err => console.error("Fehler beim Abrufen der Nachrichten:", err));
		}

		function renderMessages(messages, currentUserId, isLoadMore) {
			const previousScrollHeight = messagesArea.scrollHeight;

			if (!isLoadMore && messages.length === 0) {
				messagesContainer.innerHTML = `
					<div class="fm-chat-empty-state">
						<i class="ph ph-chat-circle" style="font-size: 3rem;"></i>
						<p>Schreibe die erste Nachricht...</p>
					</div>`;
				return;
			}

			if (messages.length === 0 && isLoadMore) {
				loadMoreBtn.style.display = 'none';
				return;
			}

			const fragment = document.createDocumentFragment();

			messages.forEach((msg) => {
				if (document.getElementById(`msg-${msg.id}`)) return;

				const wrapper = document.createElement('div');
				const isMe = parseInt(msg.sender_id) === parseInt(currentUserId);
				wrapper.id = `msg-${msg.id}`;
				wrapper.className = `fm-msg-bubble-wrapper ${isMe ? 'is-me' : 'is-other'}`;

				let formattedTime = "";
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
				metaSpan.textContent = metaText; // Sicher [1]

				const bubbleDiv = document.createElement('div');
				bubbleDiv.className = 'fm-msg-bubble';
				bubbleDiv.textContent = msg.message_text; // Komplett immun gegen XSS [1]

				wrapper.appendChild(metaSpan);
				wrapper.appendChild(bubbleDiv);
				fragment.appendChild(wrapper);
			});

			if (isLoadMore) {
				messagesContainer.insertBefore(fragment, messagesContainer.firstChild);
				messagesArea.scrollTop = messagesArea.scrollHeight - previousScrollHeight;
			} else {
				messagesContainer.appendChild(fragment);
				scrollChatToBottom();
			}
		}

		loadMoreBtn.addEventListener('click', () => {
			currentOffset += 50;
			loadMessages(true);
		});

		chatForm.addEventListener('submit', (e) => {
			e.preventDefault();
			const text = chatInput.value.trim();
			if (!text) return;

			chatInput.value = '';

			const formData = new FormData();
			formData.append('type', currentType);
			if (currentTargetId) formData.append('target_id', currentTargetId);
			formData.append('message_text', text);

			fetch(`${BASE_CHAT_URL}/sendMessage`, {
				method: 'POST',
				body: formData,
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						loadMessages(false);
					} else if (data.error) {
						alert(data.error);
					}
				})
				.catch(err => console.error("Senden fehlgeschlagen:", err));
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
});