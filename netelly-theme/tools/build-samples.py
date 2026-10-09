"""Abstract "cinematic light" frames used as temporary images (inc/samples.php).

No people, places or works are depicted, so nothing on the site reads as a real photo.
Re-generate with:
  python3 tools/build-samples.py assets/images/samples
"""

import sys
from pathlib import Path

import numpy as np
from PIL import Image, ImageFilter

PALETTES = [
	[(255, 150, 60), (40, 120, 160)],    # amber / teal
	[(220, 40, 70), (255, 170, 90)],     # crimson / warm
	[(120, 80, 255), (40, 200, 220)],    # violet / cyan
	[(255, 200, 120), (90, 60, 40)],     # tungsten
	[(60, 140, 255), (200, 220, 255)],   # night blue
	[(255, 90, 150), (90, 70, 200)],     # magenta / indigo
	[(120, 220, 170), (30, 80, 90)],     # sodium green
	[(255, 120, 40), (255, 60, 40)],     # sunset
]


def frame(w, h, palette, seed):
	rng = np.random.default_rng(seed)
	y, x = np.mgrid[0:h, 0:w].astype(np.float32)
	x /= w
	y /= h
	img = np.zeros((h, w, 3), np.float32)
	img += np.array([10, 10, 12], np.float32)
	# Soft light pools.
	for i in range(4):
		col = np.array(palette[i % len(palette)], np.float32)
		cx, cy = rng.uniform(0.1, 0.9), rng.uniform(0.15, 0.85)
		r = rng.uniform(0.18, 0.42)
		g = np.exp(-(((x - cx) * w / max(w, h)) ** 2 + ((y - cy) * h / max(w, h)) ** 2) / (2 * r * r))
		img += g[..., None] * col * rng.uniform(0.08, 0.26) * (1.0 if i < 2 else 0.5)
	# Anamorphic streak.
	sy = rng.uniform(0.3, 0.7)
	sx = rng.uniform(0.3, 0.7)
	core = np.exp(-((y - sy) ** 2) / (2 * 0.0025 ** 2)) * np.exp(-((x - sx) ** 2) / (2 * 0.22 ** 2))
	halo = np.exp(-((y - sy) ** 2) / (2 * 0.03 ** 2)) * np.exp(-((x - sx) ** 2) / (2 * 0.3 ** 2))
	img += (core * 0.22 + halo * 0.1)[..., None] * np.array(palette[0], np.float32)
	# Out-of-focus highlights.
	bokeh = np.zeros((h, w), np.float32)
	for _ in range(rng.integers(3, 9)):
		cx, cy = rng.uniform(0, w), rng.uniform(0, h)
		r = rng.uniform(0.008, 0.03) * max(w, h)
		d = np.sqrt((x * w - cx) ** 2 + (y * h - cy) ** 2)
		bokeh += np.clip(1 - (d - r) / (0.25 * r), 0, 1) * rng.uniform(0.05, 0.18)
	img += bokeh[..., None] * np.array(palette[1 % len(palette)], np.float32)
	# Vignette, filmic roll-off, grain.
	v = 1 - 0.75 * (((x - 0.5) * 1.3) ** 2 + ((y - 0.5) * 1.3) ** 2)
	img *= np.clip(v, 0.15, 1)[..., None]
	img = 255 * (1 - np.exp(-img / 140))
	img += rng.normal(0, 5, (h, w, 1))
	out = Image.fromarray(np.clip(img, 0, 255).astype(np.uint8))
	return out.filter(ImageFilter.GaussianBlur(1.2))


def main(dest):
	dest = Path(dest)
	dest.mkdir(parents=True, exist_ok=True)
	for i, pal in enumerate(PALETTES):
		frame(1600, 1000, pal, 100 + i).save(dest / f"landscape-{i + 1}.webp", quality=78, method=6)
		frame(1000, 1400, pal, 200 + i).save(dest / f"portrait-{i + 1}.webp", quality=78, method=6)


if __name__ == "__main__":
	main(sys.argv[1] if len(sys.argv) > 1 else "assets/images/samples")
