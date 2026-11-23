// OG share cards — one branded 1200×630 JPEG per project, rendered with the
// site's real fonts and grade by headless Chromium, written to dist/og/.
//
// Runs AFTER `npm run build` (the pages already point at /og/<slug>.jpg):
//   node scripts/og.mjs
// Browser resolution matches smoke.mjs: PW_CHROME overrides, else the
// playwright-installed Chromium.

import { chromium } from 'playwright';
import http from 'node:http';
import { createReadStream, existsSync, statSync, readFileSync } from 'node:fs';
import { mkdir } from 'node:fs/promises';
import { join, extname, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const DIST = join(ROOT, 'dist');
const PORT = 4699;

const MIME = { '.html': 'text/html', '.json': 'application/json', '.avif': 'image/avif', '.webp': 'image/webp', '.jpg': 'image/jpeg', '.png': 'image/png', '.woff2': 'font/woff2', '.css': 'text/css', '.js': 'text/javascript', '.svg': 'image/svg+xml' };

const FONT_ANTON = join(ROOT, 'node_modules/@fontsource/anton/files/anton-latin-400-normal.woff2');
const FONT_MONO = join(ROOT, 'node_modules/@fontsource/space-mono/files/space-mono-latin-400-normal.woff2');

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');

function cardHtml(p) {
	// The poster grammar at 1200×630: graded photo, ink gradient, wordmark,
	// colossal title with the accent full stop, mono EXIF footer.
	return `<!doctype html><html><head><meta charset="utf-8"><style>
	@font-face { font-family: Anton; src: url('/__font/anton.woff2') format('woff2'); }
	@font-face { font-family: 'Space Mono'; src: url('/__font/mono.woff2') format('woff2'); }
	* { margin: 0; box-sizing: border-box; }
	body { width: 1200px; height: 630px; background: #0B0B0B; overflow: hidden; position: relative; color: #F4F2ED; }
	img.ph { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: ${esc(p.focal || '50% 30%')};
		filter: grayscale(1) contrast(1.06) brightness(.72); }
	.grade { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(11,11,11,.35) 0%, rgba(11,11,11,0) 35%, rgba(11,11,11,.88) 100%); }
	.wm { position: absolute; left: 56px; top: 44px; font-family: Anton; font-size: 30px; letter-spacing: .22em; }
	.exif { position: absolute; right: 56px; top: 52px; font-family: 'Space Mono', monospace; font-size: 16px; letter-spacing: .2em; color: #b7b1a7; }
	.title { position: absolute; left: 56px; bottom: 96px; right: 56px; font-family: Anton; font-size: 96px; line-height: .95;
		letter-spacing: .01em; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: clip; }
	.title .dot { color: #FF3B1D; }
	.foot { position: absolute; left: 56px; bottom: 44px; font-family: 'Space Mono', monospace; font-size: 17px; letter-spacing: .22em; color: #b7b1a7; text-transform: uppercase; }
	</style></head><body>
	<img class="ph" src="${esc(p.image)}">
	<div class="grade"></div>
	<div class="wm">RAVEENTHIRAN</div>
	<div class="exif">50MM · VIENNA</div>
	<div class="title">${esc(p.title)}<span class="dot">.</span></div>
	<div class="foot">${esc([p.category, p.year].filter(Boolean).join(' · '))} — RAVEENTHIRAN.COM</div>
	</body></html>`;
}

let projects;
const srv = http.createServer((req, res) => {
	let p = decodeURIComponent(req.url.split('?')[0]);
	if (p === '/__font/anton.woff2') { res.writeHead(200, { 'content-type': 'font/woff2' }); createReadStream(FONT_ANTON).pipe(res); return; }
	if (p === '/__font/mono.woff2') { res.writeHead(200, { 'content-type': 'font/woff2' }); createReadStream(FONT_MONO).pipe(res); return; }
	const m = p.match(/^\/__card\/(\d+)$/);
	if (m) { res.writeHead(200, { 'content-type': 'text/html' }); res.end(cardHtml(projects[+m[1]])); return; }
	if (p.endsWith('/')) p += 'index.html';
	let f = join(DIST, p);
	if (!existsSync(f) || statSync(f).isDirectory()) f = join(DIST, p, 'index.html');
	if (!existsSync(f)) { res.writeHead(404); res.end(); return; }
	res.writeHead(200, { 'content-type': MIME[extname(f)] || 'application/octet-stream' });
	createReadStream(f).pipe(res);
});

projects = JSON.parse(readFileSync(join(DIST, 'og-data.json'), 'utf8'));
await new Promise((r) => srv.listen(PORT, r));
await mkdir(join(DIST, 'og'), { recursive: true });

const exe = process.env.PW_CHROME || undefined;
const browser = await chromium.launch(exe ? { executablePath: exe } : {});
const page = await browser.newPage({ viewport: { width: 1200, height: 630 }, deviceScaleFactor: 1 });

// A title that overflows 1200px shrinks until it fits (single-line poster rule).
const fit = async () => page.evaluate(() => {
	const t = document.querySelector('.title');
	let size = 96;
	while (t.scrollWidth > t.clientWidth && size > 40) { size -= 4; t.style.fontSize = size + 'px'; }
});

let done = 0;
for (let i = 0; i < projects.length; i++) {
	const p = projects[i];
	await page.goto(`http://127.0.0.1:${PORT}/__card/${i}`, { waitUntil: 'networkidle' });
	await page.evaluate(() => document.fonts.ready);
	await fit();
	await page.screenshot({ path: join(DIST, 'og', `${p.slug}.jpg`), type: 'jpeg', quality: 84 });
	done++;
}

await browser.close();
srv.close();
console.log(`og: ${done}/${projects.length} cards written to dist/og/`);
if (done !== projects.length) process.exit(1);
