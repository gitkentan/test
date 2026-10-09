// Signature effects layered on top of the README motion (all off with reduced motion):
// split hero headline, label scramble, magnetic buttons, velocity marquee, scroll-lit
// statement, cursor-following previews on the business rows.
// Scroll-linked pieces (hero drift, progress horizon, image parallax, grain) are CSS (fx.css).
import { reduceMotion, finePointer } from './env.js';

/**
 * Hero display headline → letters (runs before initIntro so its CSS animations are tracked).
 * Words stay unbreakable; the h1 keeps the readable text in aria-label.
 */
export function splitHero() {
	const h = document.querySelector('.hero__copy.is-latin');
	if (!h || reduceMotion) return;
	h.setAttribute('aria-label', h.textContent.replace(/\s+/g, ' ').trim());
	let c = 0;
	h.querySelectorAll('.hero__line-text > span').forEach((line) => {
		const text = line.textContent;
		line.textContent = '';
		line.setAttribute('aria-hidden', 'true');
		text.split(/(\s+)/).forEach((word) => {
			if (!word) return;
			if (/^\s+$/.test(word)) {
				line.append(' ');
				return;
			}
			const w = document.createElement('span');
			w.className = 'w';
			for (const ch of word) {
				const s = document.createElement('span');
				s.className = 'ch';
				s.textContent = ch;
				s.style.setProperty('--c', c++);
				w.append(s);
			}
			line.append(w);
		});
	});
	h.classList.add('is-split');
}

/**
 * Mono labels decode from random glyphs when they enter the viewport.
 */
export function initScramble() {
	if (reduceMotion) return;
	const els = document.querySelectorAll('.section-head__en .mask > span, .stats__label, .statement__label, .entry-band__label, .short-drama__label, .careers-band__label, .fund-cta__label');
	if (!els.length) return;
	const glyphs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789/+—·';
	const run = (el) => {
		const final = el.textContent;
		if (!final.trim() || /[^\x20-\x7E·—]/.test(final)) return; // Latin labels only.
		el.setAttribute('aria-label', final);
		const steps = 16;
		let f = 0;
		const tick = () => {
			f++;
			const done = Math.floor((f / steps) * final.length);
			el.textContent = [...final].map((ch, i) => (ch === ' ' || i < done ? ch : glyphs[(Math.random() * glyphs.length) | 0])).join('');
			if (f < steps) setTimeout(() => requestAnimationFrame(tick), 34);
			else el.textContent = final;
		};
		tick();
	};
	const io = new IntersectionObserver((entries) => {
		for (const e of entries) {
			if (!e.isIntersecting) continue;
			io.unobserve(e.target);
			run(e.target);
		}
	}, { rootMargin: '0px 0px -10% 0px' });
	els.forEach((el) => io.observe(el));
}

/**
 * Buttons lean toward the cursor (fine pointers only). Uses `translate`, so it composes
 * with the existing transform-based hovers.
 */
export function initMagnetic() {
	if (reduceMotion || !finePointer()) return;
	document.querySelectorAll('.btn, .site-header__cta, .hero__toggle').forEach((el) => {
		el.classList.add('is-magnetic');
		el.addEventListener('pointermove', (e) => {
			const r = el.getBoundingClientRect();
			const x = ((e.clientX - r.left) / r.width - 0.5) * 2;
			const y = ((e.clientY - r.top) / r.height - 0.5) * 2;
			el.style.translate = `${(x * 7).toFixed(2)}px ${(y * 5).toFixed(2)}px`;
		});
		el.addEventListener('pointerleave', () => {
			el.style.translate = '';
		});
	});
}

/**
 * Hero letters lift away from the cursor (fine pointers, after the intro).
 */
export function initHeroHover() {
	const h = document.querySelector('.hero__copy.is-split');
	if (!h || reduceMotion || !finePointer()) return;
	const chars = [...h.querySelectorAll('.ch')];
	let raf = 0;
	let mx = -1e4;
	let my = -1e4;
	const update = () => {
		raf = 0;
		for (const ch of chars) {
			const r = ch.getBoundingClientRect();
			const d = Math.hypot(mx - (r.left + r.width / 2), my - (r.top + r.height / 2));
			const k = Math.max(0, 1 - d / 220);
			ch.style.translate = k ? `0 ${(-k * 0.14).toFixed(3)}em` : '';
		}
	};
	const hero = h.closest('.hero');
	hero.addEventListener('pointermove', (e) => {
		mx = e.clientX;
		my = e.clientY;
		raf ||= requestAnimationFrame(update);
	});
	hero.addEventListener('pointerleave', () => {
		mx = my = -1e4;
		raf ||= requestAnimationFrame(update);
	});
}

/**
 * Marquee: constant drift; scroll velocity speeds it up and slants it.
 */
export function initMarquee() {
	const bands = [...document.querySelectorAll('.marquee')];
	if (!bands.length || reduceMotion) return;
	bands.forEach((band) => {
		const track = band.querySelector('.marquee__track');
		track.append(...[...track.children].map((n) => n.cloneNode(true)));
		let x = 0;
		let vel = 0;
		let lastY = window.scrollY;
		let visible = false;
		let last = performance.now();
		new IntersectionObserver(([e]) => {
			visible = e.isIntersecting;
		}).observe(band);
		const loop = (now) => {
			const dt = Math.min(64, now - last) / 1000;
			last = now;
			const y = window.scrollY;
			vel += ((y - lastY) / Math.max(dt, 0.001) - vel) * 0.12;
			lastY = y;
			if (visible) {
				const half = track.scrollWidth / 2;
				x -= (60 + Math.abs(vel) * 0.35) * dt * (vel < -20 ? -1 : 1);
				if (x <= -half) x += half;
				if (x > 0) x -= half;
				const skew = Math.max(-12, Math.min(12, vel * -0.008));
				track.style.transform = `translate3d(${x.toFixed(1)}px,0,0) skewX(${skew.toFixed(2)}deg)`;
			}
			requestAnimationFrame(loop);
		};
		requestAnimationFrame(loop);
	});
}

/**
 * Statement: characters light up with scroll progress through the block
 * (--p 0→1 drives each unit's opacity in CSS). One style write per frame.
 */
export function initManifesto() {
	const el = document.querySelector('[data-manifesto]');
	if (!el || reduceMotion) return;
	const text = el.querySelector('.manifesto__text');
	el.classList.add('is-scrubbed');
	let raf = 0;
	const update = () => {
		raf = 0;
		const r = text.getBoundingClientRect();
		const vh = window.innerHeight;
		// Starts when the text top reaches 85% of the screen, done when its bottom passes 45%.
		const p = (vh * 0.85 - r.top) / (r.height + vh * 0.4);
		el.style.setProperty('--p', Math.min(1, Math.max(0, p)).toFixed(4));
	};
	const onScroll = () => {
		raf ||= requestAnimationFrame(update);
	};
	new IntersectionObserver(([e]) => {
		if (e.isIntersecting) window.addEventListener('scroll', onScroll, { passive: true });
		else window.removeEventListener('scroll', onScroll);
		update();
	}).observe(el);
}

/**
 * Rows with data-preview: a floating image follows the cursor (fine pointers, ≥ 1025px).
 */
export function initHoverPreview() {
	const rows = [...document.querySelectorAll('[data-preview]')];
	if (!rows.length || reduceMotion || !finePointer() || window.innerWidth < 1025) return;
	const fig = document.createElement('div');
	fig.className = 'hover-preview';
	fig.setAttribute('aria-hidden', 'true');
	const img = document.createElement('img');
	img.alt = '';
	img.decoding = 'async';
	fig.append(img);
	document.body.append(fig);
	document.documentElement.classList.add('has-hover-preview');
	let x = 0;
	let y = 0;
	let cx = 0;
	let cy = 0;
	let raf = 0;
	const loop = () => {
		cx += (x - cx) * 0.16;
		cy += (y - cy) * 0.16;
		fig.style.transform = `translate3d(${cx.toFixed(1)}px, ${cy.toFixed(1)}px, 0) rotate(${((x - cx) * 0.03).toFixed(2)}deg)`;
		raf = Math.abs(x - cx) + Math.abs(y - cy) > 0.3 ? requestAnimationFrame(loop) : 0;
	};
	rows.forEach((row) => {
		row.addEventListener('pointerenter', (e) => {
			if (img.getAttribute('src') !== row.dataset.preview) img.src = row.dataset.preview;
			if (!fig.classList.contains('is-on')) {
				cx = x = e.clientX;
				cy = y = e.clientY;
			}
			fig.classList.add('is-on');
			raf ||= requestAnimationFrame(loop);
		});
		row.addEventListener('pointermove', (e) => {
			x = e.clientX;
			y = e.clientY;
			raf ||= requestAnimationFrame(loop);
		});
		row.addEventListener('pointerleave', () => fig.classList.remove('is-on'));
	});
}
