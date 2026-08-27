// Visual-regression / smoke check.
//
// Serves the already-built dist/ with `astro preview`, then loads the key
// routes in a real headless Chromium and:
//   • fails if any page throws an uncaught JS error (the exact class of bug
//     that used to leave the site looking broken),
//   • fails if a page is missing its <main> content,
//   • writes a full-page screenshot of every route to screenshots/ so a human
//     can eyeball the visual diff on each pull request (uploaded as an artifact).
//
// Run after `npm run build`:  node scripts/smoke.mjs
// Locally you can point at a prebuilt browser via PLAYWRIGHT_BROWSERS_PATH.

import { chromium } from 'playwright';
import { spawn } from 'node:child_process';
import { mkdir, rm } from 'node:fs/promises';
import { setTimeout as sleep } from 'node:timers/promises';

const PORT = Number(process.env.PORT || 4321);
const BASE = `http://127.0.0.1:${PORT}`;
const OUT = new URL('../screenshots/', import.meta.url).pathname;

// Routes that must exist in the built site (built with the default home layout).
const ROUTES = [
	['home', '/'],
	['work', '/work/'],
	['studio', '/about/'],
	['enquire', '/enquire/'],
	['journal', '/journal/'],
	['impressum', '/legal/impressum/'],
	['404', '/404.html'],
];

const VIEWPORTS = [
	['desktop', 1440, 900],
	['mobile', 390, 844],
];

async function waitForServer(url, tries = 60) {
	for (let i = 0; i < tries; i++) {
		try {
			const r = await fetch(url, { method: 'HEAD' });
			if (r.ok || r.status === 404) return true;
		} catch {}
		await sleep(500);
	}
	throw new Error(`preview server never came up at ${url}`);
}

async function main() {
	await rm(OUT, { recursive: true, force: true });
	await mkdir(OUT, { recursive: true });

	// Start `astro preview` serving dist/.
	const server = spawn('npm', ['run', 'preview', '--', '--port', String(PORT), '--host', '127.0.0.1'], {
		stdio: 'inherit',
		env: process.env,
	});
	const stop = () => { try { server.kill('SIGTERM'); } catch {} };
	process.on('exit', stop);

	const failures = [];
	let browser;
	try {
		await waitForServer(BASE + '/');
		// PW_CHROME lets a machine with a prebuilt Chromium (e.g. a sandbox) skip
		// Playwright's own download; CI uses `playwright install` and leaves it unset.
		browser = await chromium.launch({ executablePath: process.env.PW_CHROME || undefined });

		for (const [vpName, w, h] of VIEWPORTS) {
			const ctx = await browser.newContext({ viewport: { width: w, height: h }, deviceScaleFactor: 1 });
			for (const [name, path] of ROUTES) {
				const page = await ctx.newPage();
				const errors = [];
				page.on('pageerror', (e) => errors.push(String(e)));
				let status = 0;
				try {
					const resp = await page.goto(BASE + path, { waitUntil: 'load', timeout: 30000 });
					status = resp ? resp.status() : 0;
					// Let motion/hydration settle, then freeze animations for a stable shot.
					await sleep(600);
					// Freeze animation and force scroll-reveal / intro elements visible so
					// the flat full-page screenshot shows the real content, not the pre-reveal
					// (opacity:0) state of everything below the fold.
					await page.addStyleTag({ content:
						'*,*::before,*::after{animation:none!important;transition:none!important;caret-color:transparent!important}' +
						'[data-reveal],[data-intro],[data-letters],[data-l]{opacity:1!important;transform:none!important}' });
					await sleep(120);
					const hasMain = await page.locator('main#main, main').count();
					if (!hasMain && name !== '404') errors.push('missing <main> content');
					await page.screenshot({ path: `${OUT}${name}-${vpName}.png`, fullPage: true });
				} catch (e) {
					errors.push(`navigation failed: ${e.message}`);
				}
				if (status >= 500) errors.push(`HTTP ${status}`);
				if (errors.length) failures.push(`✗ ${vpName} ${path} — ${errors.join('; ')}`);
				else console.log(`✓ ${vpName} ${path} (${status})`);
				await page.close();
			}
			await ctx.close();
		}
	} finally {
		if (browser) await browser.close();
		stop();
	}

	if (failures.length) {
		console.error('\nVisual smoke check FAILED:\n' + failures.join('\n'));
		process.exit(1);
	}
	console.log('\nAll routes rendered cleanly — screenshots in screenshots/');
}

main().catch((e) => { console.error(e); process.exit(1); });
