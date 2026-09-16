// Folder → draft Work post. Point it at a folder of images and it creates a
// draft project in the WordPress backend (wp.m1o.at): post + attached images
// (the REST gallery uses attached media automatically) + featured image +
// optional album. Publish later in the admin, then hit ▲ Publish to deploy.
//
// Auth: a WordPress Application Password (your profile → Application
// Passwords → add one, e.g. "importer").
//
// Usage:
//   WP_USER=you WP_APP_PASS='xxxx xxxx xxxx xxxx xxxx xxxx' \
//     node scripts/wp-import.mjs /path/to/folder --title "Miri — KHM Wien" \
//       [--category "Fashion"] [--year 2026] [--location "KHM, Vienna"] \
//       [--publish] [--dry]
//
//   --title     post title (default: the folder name)
//   --category  album (work_category term; created if missing)
//   --year      shown in the archive/index (ACF "year" via meta, best effort)
//   --location  shown on the project (best effort, like --year)
//   --publish   publish immediately instead of draft
//   --dry       print the plan, touch nothing
//
// Runs anywhere with Node 18+ that can reach the WordPress URL — your laptop
// with the NAS share mounted, or on the NAS itself. Images are uploaded in
// filename order; name them 01.jpg, 02.jpg, … to control the gallery order.
// The FIRST image becomes the cover (featured image).

import { readdir, readFile } from 'node:fs/promises';
import { basename, extname, join, resolve } from 'node:path';

const WP = (process.env.WP_URL || 'https://wp.m1o.at').replace(/\/$/, '');
const USER = process.env.WP_USER || '';
const PASS = process.env.WP_APP_PASS || '';

const EXT = { '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.png': 'image/png', '.avif': 'image/avif', '.webp': 'image/webp' };

const args = process.argv.slice(2);
const flag = (name) => { const i = args.indexOf('--' + name); return i >= 0; };
const opt = (name, d = '') => { const i = args.indexOf('--' + name); return i >= 0 && args[i + 1] && !args[i + 1].startsWith('--') ? args[i + 1] : d; };
const folder = args.find((a) => !a.startsWith('--') && a !== opt('title') && a !== opt('category') && a !== opt('year') && a !== opt('location'));

if (!folder) { console.error('Usage: node scripts/wp-import.mjs /path/to/folder [--title …] [--category …] [--publish] [--dry]'); process.exit(1); }
const dir = resolve(folder);
const title = opt('title', basename(dir));
const category = opt('category');
const year = opt('year');
const location = opt('location');
const status = flag('publish') ? 'publish' : 'draft';
const dry = flag('dry');

const files = (await readdir(dir)).filter((f) => EXT[extname(f).toLowerCase()]).sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
if (!files.length) { console.error(`No images (${Object.keys(EXT).join(' ')}) found in ${dir}`); process.exit(1); }

console.log(`${dry ? '[dry] ' : ''}"${title}" ← ${files.length} image(s) from ${dir}`);
console.log(`${dry ? '[dry] ' : ''}status: ${status}${category ? ` · album: ${category}` : ''}${year ? ` · year: ${year}` : ''}${location ? ` · location: ${location}` : ''}`);
console.log(`${dry ? '[dry] ' : ''}cover: ${files[0]} · gallery order: filename order`);
if (dry) { files.forEach((f, i) => console.log(`[dry]   ${String(i + 1).padStart(2, '0')}  ${f}`)); process.exit(0); }

if (!USER || !PASS) { console.error('Set WP_USER and WP_APP_PASS (an Application Password from your WordPress profile).'); process.exit(1); }
const AUTH = 'Basic ' + Buffer.from(`${USER}:${PASS}`).toString('base64');

async function api(path, init = {}) {
	const res = await fetch(`${WP}/wp-json${path}`, { ...init, headers: { Authorization: AUTH, ...(init.headers || {}) } });
	if (!res.ok) { throw new Error(`${init.method || 'GET'} ${path} → HTTP ${res.status}: ${(await res.text()).slice(0, 300)}`); }
	return res.json();
}

// 1) album term (find or create)
let catIds = [];
if (category) {
	const found = await api(`/wp/v2/work_category?search=${encodeURIComponent(category)}&per_page=100`);
	const hit = found.find((t) => t.name.toLowerCase() === category.toLowerCase());
	const term = hit || (await api('/wp/v2/work_category', {
		method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name: category }),
	}));
	catIds = [term.id];
	console.log(`album: ${term.name} (#${term.id}${hit ? '' : ', created'})`);
}

// 2) the draft post
const meta = {};
if (year) { meta.year = year; }
if (location) { meta.location = location; }
const post = await api('/wp/v2/work', {
	method: 'POST', headers: { 'Content-Type': 'application/json' },
	body: JSON.stringify({ title, status, ...(catIds.length ? { work_category: catIds } : {}), ...(Object.keys(meta).length ? { acf: meta } : {}) }),
}).catch(async (e) => {
	// Some setups reject unknown `acf` — retry without it, fields can be set in the admin.
	if (!Object.keys(meta).length) { throw e; }
	console.log('note: year/location not accepted over REST — set them in the admin. Retrying without.');
	return api('/wp/v2/work', {
		method: 'POST', headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ title, status, ...(catIds.length ? { work_category: catIds } : {}) }),
	});
});
console.log(`post: #${post.id} "${title}" (${status})`);

// 3) upload every image attached to the post; first one becomes the cover
let cover = 0;
for (let i = 0; i < files.length; i++) {
	const f = files[i];
	const bytes = await readFile(join(dir, f));
	const media = await api(`/wp/v2/media?post=${post.id}`, {
		method: 'POST',
		headers: { 'Content-Type': EXT[extname(f).toLowerCase()], 'Content-Disposition': `attachment; filename="${f.replace(/[^\w.\-]+/g, '_')}"` },
		body: bytes,
	});
	if (i === 0) { cover = media.id; }
	console.log(`  ${String(i + 1).padStart(2, '0')}/${String(files.length).padStart(2, '0')}  ${f} → media #${media.id}`);
}
if (cover) { await api(`/wp/v2/work/${post.id}`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ featured_media: cover }) }); }

console.log(`\ndone — edit/publish here:\n${WP}/wp-admin/post.php?post=${post.id}&action=edit`);
if (status === 'draft') { console.log('It stays invisible on the live site until you Publish it and press ▲ Publish.'); }
