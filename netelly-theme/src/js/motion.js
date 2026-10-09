// README 3 — scroll motion.
// - Section heads (rule draws, heading masks up), card groups (fade/rise, 60ms stagger,
//   max 8), images (clip + inner scale): IntersectionObserver rootMargin -15%, once.
// - Hero image / video parallax: up to 12%, rAF, transform only.
// - Numbers [data-count]: count up from 0 over 1200ms (tabular-nums).
import { reduceMotion } from './env.js';

export function initReveal() {
	if (reduceMotion) return;
	const io = new IntersectionObserver((entries) => {
		for (const e of entries) {
			if (!e.isIntersecting) continue;
			const el = e.target;
			if (el.hasAttribute('data-stagger')) {
				const step = Number(el.dataset.stagger) || 60;
				[...el.children].forEach((child, i) => child.style.setProperty('--delay', `${Math.min(i, 7) * step}ms`));
			}
			el.classList.add('is-in');
			io.unobserve(el);
		}
	}, { rootMargin: '0px 0px -15% 0px' });
	document.querySelectorAll('.section-head, [data-stagger], .media').forEach((el) => {
		if (!el.closest('.hero, .work-hero')) io.observe(el);
	});
}

export function initParallax() {
	if (reduceMotion) return;
	const layers = [...document.querySelectorAll('[data-parallax]')];
	if (!layers.length) return;
	let queued = false;
	const update = () => {
		queued = false;
		for (const el of layers) {
			const box = el.parentElement.getBoundingClientRect();
			if (box.bottom < 0 || box.top > window.innerHeight) continue;
			const progress = Math.min(Math.max(-box.top / box.height, 0), 1);
			el.style.transform = `translate3d(0, ${(progress * 12).toFixed(3)}%, 0)`;
		}
	};
	window.addEventListener('scroll', () => {
		if (!queued) {
			queued = true;
			requestAnimationFrame(update);
		}
	}, { passive: true });
	update();
}

export function initCountUp() {
	const nums = document.querySelectorAll('[data-count]');
	if (!nums.length || reduceMotion) return;
	const fmt = new Intl.NumberFormat('en-US');
	const io = new IntersectionObserver((entries) => {
		for (const e of entries) {
			if (!e.isIntersecting) continue;
			io.unobserve(e.target);
			const digits = e.target.querySelector('[data-digits], .work-views__digits');
			const target = Number(e.target.dataset.count) || 0;
			if (!digits) continue;
			const t0 = performance.now();
			const tick = (now) => {
				const p = Math.min((now - t0) / 1200, 1);
				const eased = 1 - Math.pow(1 - p, 4);
				digits.textContent = fmt.format(Math.round(target * eased));
				if (p < 1) requestAnimationFrame(tick);
			};
			digits.textContent = '0';
			requestAnimationFrame(tick);
		}
	}, { rootMargin: '0px 0px -15% 0px' });
	nums.forEach((n) => io.observe(n));
}
