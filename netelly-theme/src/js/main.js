// Entry. Each module is a no-op on pages without its markup.
import { initIntro } from './intro.js';
import { initHeader } from './header.js';
import { initMenu } from './menu.js';
import { initReveal, initParallax, initCountUp } from './motion.js';
import { initHeroVideo, initShortDrama, initTrailer } from './video.js';
import { initFilter } from './filter.js';
import { initSmoothScroll } from './scroll.js';
import { initForm } from './form.js';
import { splitHero, initScramble, initMagnetic, initHeroHover, initMarquee, initManifesto, initHoverPreview, initChapters, initCursor, initThemeToggle } from './fx.js';

splitHero(); // Before the intro collects its animations.
initIntro();
initHeader();
initMenu();
initReveal();
initParallax();
initCountUp();
initHeroVideo();
initShortDrama();
initTrailer();
initFilter();
initSmoothScroll();
initForm();
initScramble();
initMagnetic();
initHeroHover();
initMarquee();
initManifesto();
initHoverPreview();
initChapters();
initCursor();
initThemeToggle();

// Article share: copy link.
document.querySelectorAll('.share__copy').forEach((btn) => {
	btn.addEventListener('click', async () => {
		try {
			await navigator.clipboard.writeText(btn.dataset.url);
			const status = btn.parentElement.querySelector('.share__status');
			if (status) status.textContent = btn.dataset.done;
		} catch (e) {
			window.prompt('URL', btn.dataset.url);
		}
	});
});

// Press kit: copy a boilerplate text.
document.querySelectorAll('.press-copy').forEach((btn) => {
	btn.addEventListener('click', async () => {
		const text = document.getElementById(btn.dataset.copy)?.textContent || '';
		const status = btn.parentElement.querySelector('.press-copy__status');
		try {
			await navigator.clipboard.writeText(text);
			if (status) status.textContent = btn.dataset.done;
		} catch (e) {
			window.prompt('', text);
		}
	});
});

document.documentElement.classList.add('motion-ready');
