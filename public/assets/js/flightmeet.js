const menuToggle = document.getElementById('fmMenuToggle');
const menu = document.getElementById('fmMenu');

if (menuToggle && menu) {
	menuToggle.addEventListener('click', () => {
		const isOpen = menu.classList.toggle('is-open');
		menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
	});
}

