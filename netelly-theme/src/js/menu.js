// SP menu (README 5): aria-expanded, focus trap, Esc to close, body scroll lock.
// The open/close animation is added in stage 5 (motion.js).

const FOCUSABLE = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function initMenu() {
	const toggle = document.querySelector('.menu-toggle');
	const menu = document.getElementById('sp-menu');
	const header = document.querySelector('.site-header');
	if (!toggle || !menu || !header) return;

	const label = toggle.querySelector('.screen-reader-text');
	let lastFocus = null;

	const focusables = () =>
		[toggle, ...menu.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null || el === toggle);

	function open() {
		lastFocus = document.activeElement;
		menu.hidden = false;
		toggle.setAttribute('aria-expanded', 'true');
		if (label) label.textContent = toggle.dataset.labelClose;
		document.body.classList.add('is-menu-open');
		document.addEventListener('keydown', onKey);
		const first = menu.querySelector(FOCUSABLE);
		if (first) first.focus();
	}

	function close({ restoreFocus = true } = {}) {
		menu.hidden = true;
		toggle.setAttribute('aria-expanded', 'false');
		if (label) label.textContent = toggle.dataset.labelOpen;
		document.body.classList.remove('is-menu-open');
		document.removeEventListener('keydown', onKey);
		if (restoreFocus) (lastFocus && lastFocus !== document.body ? lastFocus : toggle).focus();
	}

	function onKey(e) {
		if (e.key === 'Escape') {
			e.preventDefault();
			close();
			return;
		}
		if (e.key !== 'Tab') return;
		const items = focusables();
		const first = items[0];
		const last = items[items.length - 1];
		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	}

	toggle.addEventListener('click', () => (toggle.getAttribute('aria-expanded') === 'true' ? close() : open()));

	// In-page links (e.g. /company/#history) close the menu before scrolling.
	menu.addEventListener('click', (e) => {
		if (e.target.closest('a')) close({ restoreFocus: false });
	});

	// Leaving the toggle breakpoint with the menu open.
	window.matchMedia('(min-width: 1101px)').addEventListener('change', (mq) => {
		if (mq.matches && !menu.hidden) close({ restoreFocus: false });
	});
}
