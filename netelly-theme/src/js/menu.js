// README 5 — SP menu: lines → × (240ms), full-screen black overlay, items mask up
// with a 40ms stagger. aria-expanded, focus trap, Esc to close, body scroll lock.

const FOCUSABLE = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function initMenu() {
	const toggle = document.querySelector('.menu-toggle');
	const menu = document.getElementById('sp-menu');
	if (!toggle || !menu) return;

	const label = toggle.querySelector('.screen-reader-text');
	let lastFocus = null;
	const visible = (el) => el.offsetParent !== null || el === toggle;
	const focusables = () => [toggle, ...menu.querySelectorAll(FOCUSABLE)].filter(visible);
	const emit = (open) => document.dispatchEvent(new CustomEvent('netelly:menu', { detail: { open } }));

	function open() {
		lastFocus = document.activeElement;
		menu.hidden = false;
		requestAnimationFrame(() => menu.classList.add('is-open'));
		toggle.setAttribute('aria-expanded', 'true');
		if (label) label.textContent = toggle.dataset.labelClose;
		document.body.classList.add('is-menu-open');
		document.addEventListener('keydown', onKey);
		emit(true);
		const first = menu.querySelector(FOCUSABLE);
		if (first) first.focus({ preventScroll: true });
	}

	function close({ restoreFocus = true } = {}) {
		menu.classList.remove('is-open');
		menu.hidden = true;
		toggle.setAttribute('aria-expanded', 'false');
		if (label) label.textContent = toggle.dataset.labelOpen;
		document.body.classList.remove('is-menu-open');
		document.removeEventListener('keydown', onKey);
		emit(false);
		if (restoreFocus) (lastFocus && lastFocus !== document.body ? lastFocus : toggle).focus({ preventScroll: true });
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
	// Going to another page: keep the menu on screen (the chosen item lit, the others dimmed)
	// so the page change goes straight from the menu to the next page — closing it first
	// flashed the current page underneath before the transition.
	const leavesPage = (a, e) => {
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return false;
		if (a.target && a.target !== '_self') return false;
		const url = new URL(a.href, location.href);
		if (url.origin !== location.origin) return false;
		return url.pathname !== location.pathname || url.search !== location.search;
	};
	menu.addEventListener('click', (e) => {
		const a = e.target.closest('a');
		if (!a) return;
		if (leavesPage(a, e)) {
			menu.classList.add('is-leaving');
			a.closest('li')?.classList.add('is-chosen');
			return;
		}
		close({ restoreFocus: false });
	});
	// Back / forward restores this page from the cache with the menu still open.
	window.addEventListener('pageshow', (e) => {
		if (e.persisted && !menu.hidden) {
			menu.classList.remove('is-leaving');
			menu.querySelectorAll('.is-chosen').forEach((li) => li.classList.remove('is-chosen'));
			close({ restoreFocus: false });
		}
	});
	window.matchMedia('(min-width: 1101px)').addEventListener('change', (mq) => {
		if (mq.matches && !menu.hidden) close({ restoreFocus: false });
	});
}
