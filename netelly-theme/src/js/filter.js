// README 8 — works filter: no reload (?genre= via history.replaceState), cards
// re-flow with FLIP (300ms), counts on the tabs, empty message when nothing matches.
import { reduceMotion, EASE_OUT } from './env.js';

export function initFilter() {
	const root = document.querySelector('[data-filter]');
	if (!root) return;
	const tabs = [...root.querySelectorAll('.tab[data-genre]')];
	const grid = root.querySelector('.grid-works');
	const cards = [...grid.children];
	const empty = root.querySelector('.works-empty');

	const apply = (genre) => {
		const first = new Map(cards.map((c) => [c, c.getBoundingClientRect()]));
		cards.forEach((c) => c.classList.toggle('is-hidden', !!genre && c.dataset.genre !== genre));
		const shown = cards.filter((c) => !c.classList.contains('is-hidden'));
		empty.hidden = shown.length > 0;
		if (reduceMotion) return;
		shown.forEach((c) => {
			const f = first.get(c);
			const l = c.getBoundingClientRect();
			if (!f.width) {
				c.animate([{ opacity: 0, transform: 'translateY(24px)' }, { opacity: 1, transform: 'none' }], { duration: 300, easing: EASE_OUT });
				return;
			}
			const dx = f.left - l.left;
			const dy = f.top - l.top;
			if (dx || dy) c.animate([{ transform: `translate(${dx}px, ${dy}px)` }, { transform: 'none' }], { duration: 300, easing: EASE_OUT });
		});
	};

	tabs.forEach((tab) => {
		tab.addEventListener('click', (e) => {
			e.preventDefault();
			const genre = tab.dataset.genre;
			tabs.forEach((t) => {
				const on = t === tab;
				t.classList.toggle('is-current', on);
				if (on) t.setAttribute('aria-current', 'true');
				else t.removeAttribute('aria-current');
			});
			apply(genre);
			const url = new URL(window.location.href);
			if (genre) url.searchParams.set('genre', genre);
			else url.searchParams.delete('genre');
			history.replaceState(null, '', url);
		});
	});
}
