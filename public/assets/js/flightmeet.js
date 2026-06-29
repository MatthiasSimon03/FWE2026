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

		document.querySelectorAll('.fm-chat-item').forEach((item) => {
			item.addEventListener('click', () => {
				document.querySelectorAll('.fm-chat-item').forEach(el => el.classList.remove('is-active'));
				item.classList.add('is-active');

				currentType = item.dataset.chatType;
				currentTargetId = item.dataset.targetId ? parseInt(item.dataset.targetId) : null;
				currentOffset = 0;
				isInitialLoad = true;

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

		function loadMessages(isLoadMore = false) {
			const offset = isLoadMore ? currentOffset : 0;
			const url = `${BASE_CHAT_URL}/getMessages?type=${currentType}&target_id=${currentTargetId || ''}&offset=${offset}`;

			return fetch(url)
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						const wasInitial = isInitialLoad;

						renderMessages(data.messages, data.userId, isLoadMore);

						if (isLoadMore) {
							if (data.messages.length < 50) {
								loadMoreBtn.style.display = 'none';
							}
						} else if (wasInitial) {
							if (data.messages.length >= 50) {
								loadMoreBtn.style.display = 'block';
							} else {
								loadMoreBtn.style.display = 'none';
							}
							isInitialLoad = false;
						}
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

			if (isLoadMore && messages.length === 0) {
				return;
			}

			const fragment = document.createDocumentFragment();
			let newMessagesCount = 0;

			messages.forEach((msg) => {
				if (document.getElementById(`msg-${msg.id}`)) return;

				newMessagesCount++;
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

				const metaSpan = document.createElement('span');
				metaSpan.className = 'fm-msg-meta';
				metaSpan.textContent = metaText;

				const bubbleDiv = document.createElement('div');
				bubbleDiv.className = 'fm-msg-bubble';
				bubbleDiv.textContent = msg.message_text;

				wrapper.appendChild(metaSpan);
				wrapper.appendChild(bubbleDiv);
				fragment.appendChild(wrapper);
			});

			if (newMessagesCount === 0) {
				return;
			}

			if (isLoadMore) {
				messagesContainer.insertBefore(fragment, messagesContainer.firstChild);
				messagesArea.scrollTop = messagesArea.scrollHeight - previousScrollHeight;
			} else {
				const isInitialLoad = messagesContainer.children.length === 0 || messagesContainer.querySelector('.fm-chat-empty-state');
				const isNearBottom = messagesArea.scrollHeight - messagesArea.scrollTop - messagesArea.clientHeight < 150;

				const emptyState = messagesContainer.querySelector('.fm-chat-empty-state');
				if (emptyState) {
					emptyState.remove();
				}

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

		chatForm.addEventListener('submit', async (e) => {
			e.preventDefault();

			const text = chatInput.value.trim();

			if (!text) {
				return;
			}

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

				const response = await fetch(`${BASE_CHAT_URL}/sendMessage`, {
					method: 'POST',
					body: formData,
					headers: {
						'X-Requested-With': 'XMLHttpRequest'
					}
				});

				if (!response.ok) {
					throw new Error(`HTTP ${response.status}`);
				}

				const data = await response.json();

				if (!data.success) {
					throw new Error(data.error || 'Nachricht konnte nicht gesendet werden.');
				}

				chatInput.value = '';
				chatInput.focus();

				await loadMessages(false);

			} catch (error) {
				console.error('Senden fehlgeschlagen:', error);
				showError(error.message || 'Beim Senden der Nachricht ist ein Fehler aufgetreten.');
			} finally {
				if (submitBtn) {
					submitBtn.disabled = false;
				}
			}
		});

		function scrollChatToBottom() {
			messagesArea.scrollTop = messagesArea.scrollHeight;
		}

		function restartPolling() {
			clearTimeout(pollingTimeout);

			const poll = () => {
				loadMessages(false).finally(() => {
					pollingTimeout = setTimeout(poll, 3000);
				});
			};

			pollingTimeout = setTimeout(poll, 3000);
		}
	}

	// 6. DASHBOARD REGIONEN-CHART (CHART.JS)
	const regionChartEl = document.getElementById('regionChart');

	if (regionChartEl && typeof Chart !== 'undefined') {
		try {
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
				{ name: 'col-title', attr: 'data-title' },
				'col-spot',
				'col-region',
				{ name: 'col-date', attr: 'data-timestamp' }
			]
		});
	}

	// 8. DATUMS- & ZEIT-PICKER (FLATPICKR)
	const dateInput = document.getElementById('meet_date');
	const timeInput = document.getElementById('meet_time');

	if (dateInput && typeof flatpickr !== 'undefined') {
		flatpickr(dateInput, {
			locale: 'de',
			dateFormat: 'Y-m-d',
			minDate: 'today',
			allowInput: false
		});
	}

	if (timeInput && typeof flatpickr !== 'undefined') {
		flatpickr(timeInput, {
			locale: 'de',
			enableTime: true,
			noCalendar: true,
			dateFormat: 'H:i',
			time_24hr: true
		});
	}

	// 9. GENERISCHER TAB-SWITCHER (Muss direkt im Haupt-DOMContentLoaded-Block registriert werden)
	document.querySelectorAll('[data-tab-target]').forEach(button => {
		button.addEventListener('click', () => {
			const container = button.closest('.fm-detail-layout') || document;

			container.querySelectorAll('[data-tab-target]').forEach(btn => btn.classList.remove('is-active'));
			button.classList.add('is-active');

			container.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');

			const targetId = button.dataset.tabTarget;
			const targetContent = document.getElementById(targetId);
			if (targetContent) {
				targetContent.style.display = 'block';
			}

			if (targetId.includes('map') && typeof activeMapInstance !== 'undefined' && activeMapInstance) {
				setTimeout(() => {
					activeMapInstance.invalidateSize();
				}, 100);
			}
		});
	});
});

// ==========================================================================
// DEKLARATIONEN AUSSERHALB (Für globale Sichtbarkeit / window-Scope)
// ==========================================================================

let activeMapInstance = null;

function initDynamicFlightsMap(mapId) {
	const mapEl = document.getElementById(mapId);
	if (!mapEl || typeof L === 'undefined') return;

	const baseLat = mapEl.dataset.baseLat ? parseFloat(mapEl.dataset.baseLat) : null;
	const baseLng = mapEl.dataset.baseLng ? parseFloat(mapEl.dataset.baseLng) : null;
	const baseName = mapEl.dataset.baseName || '';
	const flights = JSON.parse(mapEl.dataset.flights || '[]');
	const iconUrl = mapEl.dataset.iconUrl;
	const baseUrl = mapEl.dataset.baseUrl || '';
	const fromGroupParam = mapEl.dataset.fromGroup ? `?from_group=${mapEl.dataset.fromGroup}` : '';

	const startLat = baseLat || 49.7552;
	const startLng = baseLng || 6.6394;
	const startZoom = baseLat ? 7 : 5;

	activeMapInstance = L.map(mapId).setView([startLat, startLng], startZoom);

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		attribution: '&copy; OpenStreetMap-Mitwirkende'
	}).addTo(activeMapInstance);

	const bounds = [];

	if (baseLat && baseLng) {
		const baseMarker = L.marker([baseLat, baseLng]).addTo(activeMapInstance);
		bounds.push([baseLat, baseLng]);
	}

	let flightIcon = null;
	if (iconUrl) {
		flightIcon = L.icon({
			iconUrl: iconUrl,
			iconSize: [36, 36],
			iconAnchor: [18, 36],
			popupAnchor: [0, -36]
		});
	}

	flights.forEach(flight => {
		if (flight.latitude && flight.longitude) {
			const lat = parseFloat(flight.latitude);
			const lng = parseFloat(flight.longitude);

			const markerOptions = flightIcon ? { icon: flightIcon } : {};
			const marker = L.marker([lat, lng], markerOptions).addTo(activeMapInstance);

			const dateStr = new Date(flight.meet_date).toLocaleDateString('de-DE');
			const popupContent = `
            <div style="font-size: 0.9rem;">
                <h4 style="margin: 0 0 4px 0;">
                    <a href="${baseUrl}flightmeet/meetups/${flight.id}${fromGroupParam}" style="color: var(--color-primary); font-weight: 700; text-decoration: none;">
                        ${flight.title}
                    </a>
                </h4>
                <p style="margin: 2px 0;"><i class="ph ph-map-pin" style="vertical-align: middle; margin-right: 4px;"></i>${flight.location}</p>
                <p style="margin: 2px 0;"><i class="ph ph-calendar" style="vertical-align: middle; margin-right: 4px;"></i>${dateStr} - ${flight.meet_time.substring(0,5)} Uhr</p>
            </div>
        `;
			marker.bindPopup(popupContent);
			bounds.push([lat, lng]);
		}
	});

}

class DynamicFlightCalendar {
	constructor(containerId, flightsData, baseUrl, groupId = '') {
		this.grid = document.getElementById(`${containerId}-grid`);
		this.title = document.getElementById(`${containerId}-title`);
		this.flightsData = flightsData;
		this.baseUrl = baseUrl;
		this.groupId = groupId;
		this.currentDate = new Date();

		this.monate = [
			"Januar", "Februar", "März", "April", "Mai", "Juni",
			"Juli", "August", "September", "Oktober", "November", "Dezember"
		];
	}

	render() {
		if (!this.grid || !this.title) return;

		this.grid.innerHTML = '';
		const year = this.currentDate.getFullYear();
		const month = this.currentDate.getMonth();

		this.title.innerText = `${this.monate[month]} ${year}`;

		const wochentage = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
		wochentage.forEach(day => {
			const el = document.createElement('div');
			el.className = 'fm-calendar-weekday';
			el.innerText = day;
			this.grid.appendChild(el);
		});

		const firstDayIndex = new Date(year, month, 1).getDay();
		const leadDays = firstDayIndex === 0 ? 6 : firstDayIndex - 1;

		const daysInMonth = new Date(year, month + 1, 0).getDate();
		const daysInPrevMonth = new Date(year, month, 0).getDate();

		for (let i = leadDays - 1; i >= 0; i--) {
			const el = document.createElement('div');
			el.className = 'fm-calendar-day other-month';
			el.innerHTML = `<span class="fm-calendar-day-num">${daysInPrevMonth - i}</span>`;
			this.grid.appendChild(el);
		}

		const today = new Date();
		for (let day = 1; day <= daysInMonth; day++) {
			const el = document.createElement('div');
			el.className = 'fm-calendar-day';

			if (today.getDate() === day && today.getMonth() === month && today.getFullYear() === year) {
				el.classList.add('today');
			}

			el.innerHTML = `<span class="fm-calendar-day-num">${day}</span>`;

			const checkMonth = String(month + 1).padStart(2, '0');
			const checkDay = String(day).padStart(2, '0');
			const cellDateStr = `${year}-${checkMonth}-${checkDay}`;

			const dayEvents = this.flightsData.filter(flight => flight.meet_date === cellDateStr);

			if (dayEvents.length > 0) {
				const eventsDiv = document.createElement('div');
				eventsDiv.className = 'fm-calendar-events';

				dayEvents.forEach(evt => {
					const evtLink = document.createElement('a');
					const statusClass = evt.status || 'geplant';
					evtLink.className = `fm-calendar-event fm-calendar-event--${statusClass}`;

					const fromGroupParam = this.groupId ? `?from_group=${this.groupId}` : '';
					evtLink.href = `${this.baseUrl}flightmeet/meetups/${evt.id}${fromGroupParam}`;

					const timeStr = evt.meet_time ? evt.meet_time.substring(0, 5) : '00:00';
					evtLink.title = `${evt.title} (${timeStr} Uhr)`;
					evtLink.innerText = `${timeStr} - ${evt.title}`;
					eventsDiv.appendChild(evtLink);
				});

				el.appendChild(eventsDiv);
			}

			this.grid.appendChild(el);
		}

		const totalCells = this.grid.children.length - 7;
		const remainingCells = (7 - (totalCells % 7)) % 7;
		for (let i = 1; i <= remainingCells; i++) {
			const el = document.createElement('div');
			el.className = 'fm-calendar-day other-month';
			el.innerHTML = `<span class="fm-calendar-day-num">${i}</span>`;
			this.grid.appendChild(el);
		}
	}

	prev() {
		this.currentDate.setMonth(this.currentDate.getMonth() - 1);
		this.render();
	}

	next() {
		this.currentDate.setMonth(this.currentDate.getMonth() + 1);
		this.render();
	}
}

// Global registrieren für window-Scope
window.initDynamicFlightsMap = initDynamicFlightsMap;
window.DynamicFlightCalendar = DynamicFlightCalendar;