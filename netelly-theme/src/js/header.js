// README 4 — header: transparent over the hero, solid (ground colour + 1px rule) once
// the hero has passed (180ms), hides on scroll down, returns on scroll up, stays while
// the menu is open or focus is inside it.

export function initHeader() {
	const header = document.querySelector('.site-header');
	if (!header) return;
	const light = header.dataset.variant === 'light';
	const hero = document.querySelector('.hero, .page-hero, .work-hero');
	let lastY = window.scrollY;
	let queued = false;

	const update = () => {
		queued = false;
		const y = window.scrollY;
		const h = header.offsetHeight;
		const heroEnd = hero ? hero.offsetTop + hero.offsetHeight - h : 0;
		header.dataset.state = !light && y < heroEnd ? 'top' : 'solid';

		if (document.body.classList.contains('is-menu-open') || header.contains(document.activeElement)) {
			header.dataset.hidden = 'false';
		} else if (y <= h) {
			header.dataset.hidden = 'false';
		} else if (y - lastY > 4) {
			header.dataset.hidden = 'true';
		} else if (lastY - y > 4) {
			header.dataset.hidden = 'false';
		}
		lastY = y;
	};

	window.addEventListener('scroll', () => {
		if (!queued) {
			queued = true;
			requestAnimationFrame(update);
		}
	}, { passive: true });
	window.addEventListener('resize', update, { passive: true });
	header.addEventListener('focusin', () => (header.dataset.hidden = 'false'));
	update();
}
