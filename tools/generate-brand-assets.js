/**
 * Session ブランドアセット生成スクリプト。
 *
 * この astroid スパークルが Session の正式 symbol（仕様書 §21）。
 * app icon / splash / adaptive icon / favicon をここから決定的に再生成する。
 * 同じ形状を src/ui/components/SessionSymbol.tsx がベクターで持つので、
 * 形を変える場合は両方を合わせて更新すること。
 *
 *   node tools/generate-brand-assets.js
 */
const fs = require('fs');
const path = require('path');
const zlib = require('zlib');

const GREEN = [0x32, 0xf7, 0x83];
const NEAR_BLACK = [0x08, 0x0b, 0x0b];

/**
 * Session symbol = astroid（4方向に尖った凹型スパークル）。
 * |x|^k + |y|^k <= 1 (k < 1) の内側を塗る。k を小さくするほど鋭くなる。
 */
const SHARPNESS = 0.42;

function inSymbol(nx, ny) {
  return Math.pow(Math.abs(nx), SHARPNESS) + Math.pow(Math.abs(ny), SHARPNESS) <= 1;
}

/** 4x4 スーパーサンプリングでアンチエイリアスした symbol のカバレッジを返す。 */
function coverage(px, py, size, scale) {
  const half = size / 2;
  const radius = (size * scale) / 2;
  const SS = 4;
  let hits = 0;
  for (let sy = 0; sy < SS; sy++) {
    for (let sx = 0; sx < SS; sx++) {
      const x = px + (sx + 0.5) / SS - half;
      const y = py + (sy + 0.5) / SS - half;
      if (inSymbol(x / radius, y / radius)) hits++;
    }
  }
  return hits / (SS * SS);
}

function renderSymbol({ size, scale, background }) {
  const rgba = Buffer.alloc(size * size * 4);
  for (let y = 0; y < size; y++) {
    for (let x = 0; x < size; x++) {
      const a = coverage(x, y, size, scale);
      const i = (y * size + x) * 4;
      if (background) {
        // 背景の上に symbol を合成（完全不透明）
        for (let c = 0; c < 3; c++) {
          rgba[i + c] = Math.round(background[c] * (1 - a) + GREEN[c] * a);
        }
        rgba[i + 3] = 255;
      } else {
        rgba[i] = GREEN[0];
        rgba[i + 1] = GREEN[1];
        rgba[i + 2] = GREEN[2];
        rgba[i + 3] = Math.round(a * 255);
      }
    }
  }
  return rgba;
}

function renderSolid(size, color) {
  const rgba = Buffer.alloc(size * size * 4);
  for (let i = 0; i < size * size; i++) {
    rgba[i * 4] = color[0];
    rgba[i * 4 + 1] = color[1];
    rgba[i * 4 + 2] = color[2];
    rgba[i * 4 + 3] = 255;
  }
  return rgba;
}

function crc32(buf) {
  let c;
  const table = crc32.table || (crc32.table = (() => {
    const t = new Int32Array(256);
    for (let n = 0; n < 256; n++) {
      c = n;
      for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
      t[n] = c;
    }
    return t;
  })());
  let crc = -1;
  for (let i = 0; i < buf.length; i++) crc = (crc >>> 8) ^ table[(crc ^ buf[i]) & 0xff];
  return (crc ^ -1) >>> 0;
}

function chunk(type, data) {
  const len = Buffer.alloc(4);
  len.writeUInt32BE(data.length);
  const body = Buffer.concat([Buffer.from(type, 'ascii'), data]);
  const crc = Buffer.alloc(4);
  crc.writeUInt32BE(crc32(body));
  return Buffer.concat([len, body, crc]);
}

function encodePng(rgba, size) {
  const raw = Buffer.alloc((size * 4 + 1) * size);
  for (let y = 0; y < size; y++) {
    raw[y * (size * 4 + 1)] = 0; // filter: none
    rgba.copy(raw, y * (size * 4 + 1) + 1, y * size * 4, (y + 1) * size * 4);
  }
  const ihdr = Buffer.alloc(13);
  ihdr.writeUInt32BE(size, 0);
  ihdr.writeUInt32BE(size, 4);
  ihdr[8] = 8; // bit depth
  ihdr[9] = 6; // truecolour with alpha
  return Buffer.concat([
    Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]),
    chunk('IHDR', ihdr),
    chunk('IDAT', zlib.deflateSync(raw, { level: 9 })),
    chunk('IEND', Buffer.alloc(0)),
  ]);
}

const out = (name, buf) => {
  const file = path.join(__dirname, '..', 'assets', name);
  fs.writeFileSync(file, buf);
  console.log(`${name}  ${(buf.length / 1024).toFixed(1)}KB`);
};

fs.mkdirSync(path.join(__dirname, '..', 'assets'), { recursive: true });

// App icon: near-black ground + green symbol
out('icon.png', encodePng(renderSymbol({ size: 1024, scale: 0.78, background: NEAR_BLACK }), 1024));
// Splash / adaptive foreground: transparent ground + green symbol
out('splash-icon.png', encodePng(renderSymbol({ size: 1024, scale: 0.62, background: null }), 1024));
out('android-icon-foreground.png', encodePng(renderSymbol({ size: 1024, scale: 0.52, background: null }), 1024));
out('android-icon-background.png', encodePng(renderSolid(1024, NEAR_BLACK), 1024));
out('android-icon-monochrome.png', encodePng(renderSymbol({ size: 1024, scale: 0.52, background: null }), 1024));
out('favicon.png', encodePng(renderSymbol({ size: 64, scale: 0.86, background: NEAR_BLACK }), 64));
