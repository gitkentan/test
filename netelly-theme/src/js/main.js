// Entry. Each module is a no-op on pages without its markup.
import { initIntro } from './intro.js';
import { initHeader } from './header.js';
import { initMenu } from './menu.js';
import { initReveal, initParallax, initCountUp } from './motion.js';
import { initHeroVideo, initShortDrama, initTrailer } from './video.js';
import { initFilter } from './filter.js';
import { initSmoothScroll } from './scroll.js';

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

document.documentElement.classList.add('motion-ready');
