// README 1 — top page opening, once per session (shortened afterwards).
// 0–300ms black → horizon grows from the centre (900ms) → label / copy lines mask up
// (700ms, 80ms stagger) → background fades in (1200ms). Total ≤ 1.8s.
// Any click / scroll / key skips to the end.
import { EASE_IO, EASE_OUT } from './env.js';

export function initIntro() {
	const root = document.documentElement;
	if (!root.classList.contains('intro')) return;
	const hero = document.querySelector('.hero');
	const done = () => root.classList.remove('intro', 'intro-full', 'intro-short');
	if (!hero) return done();

	const full = root.classList.contains('intro-full');
	const T = full
		? { delay: 300, line: 900, mask: 700, stagger: 80, media: 1200 }
		: { delay: 0, line: 400, mask: 400, stagger: 40, media: 400 };
	const anims = [];
	const run = (el, frames, opts) => el && anims.push(el.animate(frames, { fill: 'both', ...opts }));

	run(hero.querySelector('.hero__line'), [{ transform: 'scaleX(0)' }, { transform: 'scaleX(1)' }], { duration: T.line, delay: T.delay, easing: EASE_IO });
	const t0 = T.delay + T.line / 2;
	const masks = [...hero.querySelectorAll('.mask > span')];
	masks.forEach((m, i) => run(m, [{ transform: 'translateY(100%)' }, { transform: 'none' }], { duration: T.mask, delay: t0 + i * T.stagger, easing: EASE_OUT }));
	run(hero.querySelector('.hero__cta'), [{ opacity: 0 }, { opacity: 1 }], { duration: T.mask, delay: t0 + masks.length * T.stagger, easing: EASE_OUT });
	run(document.querySelector('.site-header'), [{ opacity: 0 }, { opacity: 1 }], { duration: T.mask, delay: t0, easing: EASE_OUT });
	run(hero.querySelector('.hero__media'), [{ opacity: 0 }, { opacity: 1 }], { duration: T.media, delay: T.delay, easing: 'linear' });

	// Animations now hold the start state; the CSS pre-hide can go.
	done();
	try {
		sessionStorage.setItem('netelly_intro_seen', '1');
	} catch (e) {
		/* private mode */
	}

	const skip = () => anims.forEach((a) => a.finish());
	const events = ['pointerdown', 'wheel', 'touchstart', 'keydown'];
	events.forEach((ev) => window.addEventListener(ev, skip, { once: true, passive: true }));
	Promise.all(anims.map((a) => a.finished)).then(() => {
		anims.forEach((a) => a.cancel());
		events.forEach((ev) => window.removeEventListener(ev, skip));
	});
}
