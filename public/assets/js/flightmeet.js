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
	if (!cardsView || !tableView) {
		return;
	}

	const isTable = view === 'table';
	cardsView.classList.toggle('fm-view-hidden', isTable);
	tableView.classList.toggle('fm-view-hidden', !isTable);

	viewButtons.forEach((button) => {
		const isActive = button.dataset.view === view;
		button.classList.toggle('is-active', isActive);
		button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
	});
};

const isMobile = window.matchMedia('(max-width: 768px)').matches;
const storedView = localStorage.getItem(VIEW_STORAGE_KEY) || 'cards';
const initialView = isMobile ? 'cards' : storedView;
setActiveView(initialView);

viewButtons.forEach((button) => {
	button.addEventListener('click', () => {
		const view = button.dataset.view || 'cards';
		const nextView = window.matchMedia('(max-width: 768px)').matches ? 'cards' : view;
		localStorage.setItem(VIEW_STORAGE_KEY, nextView);
		setActiveView(nextView);
	});
});

if (window.matchMedia('(max-width: 768px)').matches) {
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
	// Standard-Fokus (z. B. Chiemsee-Region / Hochries)
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

	// Dynamisches Auslesen des benutzerdefinierten Icons (Paraglider)
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

	// Falls bereits Koordinaten vorhanden waren (z.B. Edit-Modus oder nach Validierungsfehler)
	if (hasInitialMarker) {
		marker = L.marker([initialLat, initialLng], markerOptions).addTo(mapPicker);
		updateIndicator(initialLat, initialLng);
	}

	// Klick-Event auf der Karte abfangen
	mapPicker.on('click', function(e) {
		const lat = e.latlng.lat;
		const lng = e.latlng.lng;

		if (marker === null) {
			marker = L.marker([lat, lng], markerOptions).addTo(mapPicker);
		} else {
			marker.setLatLng([lat, lng]);
		}

		// Inputs im Formular aktualisieren
		if (latInput) latInput.value = lat.toFixed(6);
		if (lngInput) lngInput.value = lng.toFixed(6);

		updateIndicator(lat, lng);
	});

	// Automatische Kartenausrichtung basierend auf der Region
	const regionInput = document.getElementById('region');

	if (regionInput) {
		regionInput.addEventListener('change', function() {
			const query = this.value.trim();
			if (query.length < 3) return; // Zu kurze Eingaben ignorieren

			// Kostenlose OpenStreetMap-Suche aufrufen
			fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
				.then(response => response.json())
				.then(data => {
					if (data && data.length > 0) {
						const lat = parseFloat(data[0].lat);
						const lng = parseFloat(data[0].lon);

						// Karte zur gefundenen Region verschieben und hineinzoomen
						mapPicker.flyTo([lat, lng], 11, {
							duration: 1.5
						});
					}
				})
				.catch(err => console.error("Fehler bei der Ortssuche:", err));
		});
	}

	// Hilfsfunktion zur Aktualisierung des Textes unter der Karte
	function updateIndicator(lat, lng) {
		const indicator = document.getElementById('coords-indicator');
		if (indicator) {
			indicator.innerHTML = `📍 Markierter Startplatz: <strong>${lat.toFixed(4)}, ${lng.toFixed(4)}</strong>`;
			indicator.style.color = 'var(--color-primary)';
			indicator.style.fontWeight = '500';
		}
	}
}