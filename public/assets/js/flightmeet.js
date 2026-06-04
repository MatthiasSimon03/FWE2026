const menuToggle = document.getElementById('fmMenuToggle');
const menu = document.getElementById('fmMenu');

if (menuToggle && menu) {
	menuToggle.addEventListener('click', () => {
		const isOpen = menu.classList.toggle('is-open');
		menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
	});
}

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

document.querySelectorAll('input[data-auto-submit="status"]').forEach((checkbox) => {
	checkbox.addEventListener('change', () => {
		const form = checkbox.closest('form');
		if (form) {
			form.requestSubmit();
		}
	});
});

if (window.matchMedia('(max-width: 768px)').matches) {
	const cardsBtn = document.querySelector('.fm-toggle-btn[data-view="cards"]');
	if (cardsBtn) cardsBtn.click();
}
