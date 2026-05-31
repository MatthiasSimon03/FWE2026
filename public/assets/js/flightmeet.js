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

const storedView = localStorage.getItem(VIEW_STORAGE_KEY) || 'cards';
setActiveView(storedView);

viewButtons.forEach((button) => {
	button.addEventListener('click', () => {
		const view = button.dataset.view || 'cards';
		localStorage.setItem(VIEW_STORAGE_KEY, view);
		setActiveView(view);
	});
});

