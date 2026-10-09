// README 7 — video.
// Hero / work KV: muted autoplay loop playsinline, poster only with Save-Data, slow
// connections and reduced motion (work KV: also on SP). The top hero plays on SP too,
// using data-src-sp when set; large high-density screens on a fast line get data-src-hd.
// Starts after the page has loaded (keeps LCP on the
// poster). Paused off screen; pause / play toggle (WCAG 2.2.2).
// Short dramas: muted play on hover (fine pointer); on SP only the frame in view plays.
// Trailer: full-screen dialog, youtube-nocookie iframe created on click, Esc /
// backdrop closes and focus returns to the button.
import { reduceMotion, saveData, isSP, finePointer } from './env.js';

const play = (v) => {
	if (!v.src && v.dataset.src) v.src = v.dataset.src;
	return v.play().then(() => v.classList.add('is-playing')).catch(() => {});
};

const slowNet = () => /(^|-)2g$/.test(navigator.connection?.effectiveType || '');
// HD: ≥ 2400 device pixels wide and, where the browser reports it, a 4G line ≥ 8 Mbps.
const wantsHD = () => {
	const c = navigator.connection;
	return window.innerWidth * (window.devicePixelRatio || 1) >= 2400 && (!c || (c.effectiveType === '4g' && (c.downlink || 10) >= 8));
};

export function initHeroVideo() {
	const v = document.querySelector('.hero__video, .work-hero__video');
	if (!v || reduceMotion || saveData || slowNet()) return;
	if (isSP()) {
		if (!v.classList.contains('hero__video')) return;
		if (v.dataset.srcSp) v.dataset.src = v.dataset.srcSp;
	} else if (v.dataset.srcHd && wantsHD()) {
		v.dataset.src = v.dataset.srcHd;
	}
	if (document.readyState !== 'complete') {
		window.addEventListener('load', () => initHeroVideo(), { once: true });
		return;
	}
	const btn = document.querySelector('.hero__toggle');
	const label = btn && btn.querySelector('.screen-reader-text');
	let userPaused = false;
	new IntersectionObserver(([e]) => {
		if (e.isIntersecting && !userPaused) {
			play(v).then(() => btn && (btn.hidden = false));
		} else {
			v.pause();
		}
	}).observe(v);
	if (btn) {
		btn.addEventListener('click', () => {
			userPaused = !v.paused;
			if (userPaused) v.pause();
			else play(v);
			btn.setAttribute('aria-pressed', String(userPaused));
			if (label) label.textContent = userPaused ? btn.dataset.labelPlay : btn.dataset.labelPause;
		});
	}
}

export function initShortDrama() {
	const frames = [...document.querySelectorAll('.short-drama__frame')].filter((f) => f.querySelector('video'));
	if (!frames.length || reduceMotion || saveData) return;
	const stop = (v) => {
		v.pause();
		v.classList.remove('is-playing');
	};
	if (finePointer()) {
		frames.forEach((f) => {
			const v = f.querySelector('video');
			f.addEventListener('mouseenter', () => play(v));
			f.addEventListener('mouseleave', () => stop(v));
			f.addEventListener('focusin', () => play(v));
			f.addEventListener('focusout', () => stop(v));
		});
		return;
	}
	// Touch: the most visible frame plays (≥ 60% in view).
	const ratios = new Map();
	const io = new IntersectionObserver((entries) => {
		entries.forEach((e) => ratios.set(e.target, e.intersectionRatio));
		let best = null;
		let bestRatio = 0.6;
		ratios.forEach((r, el) => {
			if (r >= bestRatio) {
				best = el;
				bestRatio = r;
			}
		});
		frames.forEach((f) => (f === best ? play(f.querySelector('video')) : stop(f.querySelector('video'))));
	}, { threshold: [0, 0.6, 0.8, 1] });
	frames.forEach((f) => io.observe(f));
}

export function initTrailer() {
	const dialog = document.querySelector('.trailer-modal');
	const trigger = document.querySelector('[data-trailer]');
	if (!dialog || !trigger || typeof dialog.showModal !== 'function') return;
	const frame = dialog.querySelector('.trailer-modal__frame');
	const closeBtn = dialog.querySelector('.trailer-modal__close');
	trigger.addEventListener('click', (e) => {
		const id = trigger.dataset.trailer;
		if (!/^[\w-]{11}$/.test(id)) return;
		e.preventDefault();
		const iframe = document.createElement('iframe');
		iframe.src = `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&rel=0&playsinline=1`;
		iframe.title = dialog.getAttribute('aria-label') || 'Trailer';
		iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
		iframe.allowFullscreen = true;
		frame.replaceChildren(iframe);
		dialog.showModal();
		document.dispatchEvent(new CustomEvent('netelly:menu', { detail: { open: true } }));
		closeBtn.focus();
	});
	closeBtn.addEventListener('click', () => dialog.close());
	dialog.addEventListener('click', (e) => {
		if (e.target === dialog) dialog.close();
	});
	dialog.addEventListener('close', () => {
		frame.replaceChildren();
		document.dispatchEvent(new CustomEvent('netelly:menu', { detail: { open: false } }));
		trigger.focus();
	});
}
