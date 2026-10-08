// README 1 — top page opening, once per session (shortened afterwards).
// The sequence itself is CSS (motion.css, "1. Intro") so it starts with the first paint;
// this module only remembers the session, skips on click / scroll / key, and cleans up.
export function initIntro() {
	const root = document.documentElement;
	if (!root.classList.contains('intro')) return;
	const done = () => root.classList.remove('intro', 'intro-full', 'intro-short');
	try {
		sessionStorage.setItem('netelly_intro_seen', '1');
	} catch (e) {
		/* private mode */
	}

	const scope = [document.querySelector('.hero'), document.querySelector('.site-header')].filter(Boolean);
	const anims = document.getAnimations().filter((a) => a.effect?.target && scope.some((el) => el.contains(a.effect.target)) && ['line-grow', 'mask-up', 'fade-in', 'cover-out'].includes(a.animationName));
	if (!anims.length) return done();

	const skip = () => anims.forEach((a) => a.finish());
	const events = ['pointerdown', 'wheel', 'touchstart', 'keydown'];
	events.forEach((ev) => window.addEventListener(ev, skip, { once: true, passive: true }));
	Promise.all(anims.map((a) => a.finished.catch(() => {}))).then(() => {
		done();
		events.forEach((ev) => window.removeEventListener(ev, skip));
	});
}
