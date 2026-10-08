import { defineConfig } from 'vite';
import { resolve } from 'node:path';

const src = resolve(import.meta.dirname, 'netelly-theme/src');

// src/ -> assets/dist/ (hashed files + .vite/manifest.json read by inc/enqueue.php).
export default defineConfig({
	root: src,
	base: './',
	publicDir: false,
	build: {
		outDir: resolve(import.meta.dirname, 'netelly-theme/assets/dist'),
		emptyOutDir: true,
		manifest: true,
		target: 'es2020',
		cssMinify: true,
		rollupOptions: {
			input: {
				main: resolve(src, 'js/main.js'),
				style: resolve(src, 'css/main.css'),
			},
		},
	},
});
