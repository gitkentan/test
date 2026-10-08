// README 3 — Lenis smooth scroll (lerp .1). Off on SP / touch and with reduced motion.
// In-page anchors scroll with the header offset; menu / dialog pause scrolling.
import Lenis from 'lenis';
import { reduceMotion } from './env.js';

export function initSmoothScroll() {
	if (reduceMotion || window.matchMedia('(max-width: 768px), (pointer: coarse)').matches) return;
	const lenis = new Lenis({ lerp: 0.1 });
	const raf = (t) => {
		lenis.raf(t);
		requestAnimationFrame(raf);
	};
	requestAnimationFrame(raf);

	document.addEventListener('netelly:menu', (e) => (e.detail.open ? lenis.stop() : lenis.start()));
	document.addEventListener('click', (e) => {
		const a = e.target.closest('a[href*="#"]');
		if (!a) return;
		const url = new URL(a.href, window.location.href);
		if (url.pathname !== window.location.pathname || !url.hash) return;
		const target = document.getElementById(decodeURIComponent(url.hash.slice(1)));
		if (!target) return;
		e.preventDefault();
		lenis.scrollTo(target, { offset: -(document.querySelector('.site-header')?.offsetHeight || 0) });
		history.pushState(null, '', url.hash);
	});
}
