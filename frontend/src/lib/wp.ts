/**
 * WordPress data layer (headless). Queried at BUILD time by Astro's SSG.
 * If the API is unreachable (NAS offline, or the sandbox proxy blocking the
 * host), every function falls back to the built-in SAMPLE set so the build
 * still succeeds — never a broken build, never a blank site.
 *
 * Override the endpoint at build time with:  WP_BASE=... npm run build
 */
const WP_BASE = (import.meta.env.WP_BASE as string) || 'https://wp.m1o.at/wp-json/wp/v2';

export interface GalleryImage {
	src: string;
	w: number;
	h: number;
	srcset?: string;
}

export interface Credit { role: string; name: string; url: string; }
export interface Term { name: string; slug: string; }

export interface Project {
	slug: string;
	title: string;
	category: string;
	year: string;
	client: string;
	location: string;
	website: string;
	services: string[];
	series?: Term[];
	credits: Credit[];
	featured_home: boolean;
	focal: string;            // CSS object-position, e.g. "50% 30%"
	image: string;            // featured image URL ('' if none)
	imageW?: number;          // featured image width (undefined if unknown)
	imageH?: number;          // featured image height (undefined if unknown)
	imageSrcset?: string;     // responsive srcset from WordPress sizes ('' if none)
	content: string;          // rendered HTML body ('' if none)
	gallery: GalleryImage[];  // additional images attached to the project
	ogImage: string;
}

/* ── offline / first-run fallback ──
   Uses the photographer's real frames bundled in /public/sample so the site
   looks finished even when the WordPress API is unreachable at build time.
   On a live build these are replaced by the WordPress Work archive. */
const S = (n: string) => '/sample/' + n;
function gal(...names: string[]): GalleryImage[] { return names.map((n) => ({ src: S(n), w: 0, h: 0 })); }
const SAMPLE: Project[] = [
	{ slug: 'miri-khm', title: 'Miri — KHM Wien', category: 'Fashion', year: '2026', client: 'Miri', location: 'KHM, Vienna', website: '', services: ['Fashion', 'Editorial'], series: [{ name: 'Museumsquartier', slug: 'museumsquartier' }], credits: [{ role: 'Model', name: 'Miri', url: '' }, { role: 'Styling', name: 'Miri', url: '' }, { role: 'Photography', name: 'Nishuthan Raveenthiran', url: '' }], featured_home: true, focal: '50% 20%', image: S('photos-1787300281346-72v4.avif'), content: '<p>One winter afternoon in the Kunsthistorisches Museum. Mesh against marble, gold light through the cupola — no set, no retouch theatrics.</p>', gallery: gal('photos-1787300281436-xhhr.avif', 'photos-1787300281346-72v4.avif', 'photos-1787300281605-uqp5.avif', 'photos-1787300237173-kdba.avif'), ogImage: '' },
	{ slug: 'sarah-noir', title: 'Sarah — Noir', category: 'B&W', year: '2024', client: '', location: 'Vienna', website: '', services: ['Portrait'], credits: [{ role: 'Model', name: 'Sarah', url: '' }, { role: 'Photography', name: 'Nishuthan Raveenthiran', url: '' }], featured_home: true, focal: '50% 20%', image: S('photos-1787300135405-cc8f.avif'), content: '', gallery: gal('photos-1787300135405-cc8f.avif', 'photos-1787300135360-vkq6.avif'), ogImage: '' },
	{ slug: 'luna-innere-stadt', title: 'Luna — Innere Stadt', category: 'Portrait', year: '2025', client: '', location: 'Vienna', website: '', services: ['Street portrait'], credits: [{ role: 'Photography', name: 'Nishuthan Raveenthiran', url: '' }], featured_home: true, focal: '50% 20%', image: S('photos-1787300113403-jefw.avif'), content: '', gallery: gal('photos-1787300113403-jefw.avif'), ogImage: '' },
	{ slug: 'sara', title: 'Sara', category: 'Portrait', year: '2025', client: '', location: 'Vienna', website: '', services: ['Portrait'], credits: [{ role: 'Photography', name: 'Nishuthan Raveenthiran', url: '' }], featured_home: true, focal: '50% 20%', image: S('photos-1787300171349-dd0r.avif'), content: '', gallery: gal('photos-1787300171349-dd0r.avif'), ogImage: '' },
	{ slug: 'dianlx-viecc', title: 'Dianlx — VIECC', category: 'Event', year: '2024', client: 'VIECC', location: 'Vienna', website: '', services: ['Event'], credits: [], featured_home: false, focal: '50% 20%', image: S('photos-1787300135316-3app.avif'), content: '', gallery: gal('photos-1787300135316-3app.avif'), ogImage: '' },
	{ slug: 'milky-viecc', title: 'Milky — VIECC', category: 'Event', year: '2024', client: 'VIECC', location: 'Vienna', website: '', services: ['Event'], credits: [], featured_home: false, focal: '50% 20%', image: S('photos-1787300135360-vkq6.avif'), content: '', gallery: gal('photos-1787300135360-vkq6.avif'), ogImage: '' },
	{ slug: 'study-in-gold', title: 'Study in Gold', category: 'Fashion', year: '2025', client: '', location: 'Vienna', website: '', services: ['Fashion'], credits: [], featured_home: false, focal: '50% 20%', image: S('photos-1787300200344-ujl5.avif'), content: '', gallery: gal('photos-1787300200344-ujl5.avif'), ogImage: '' },
	{ slug: 'self-love', title: 'Self Love', category: 'Personal', year: '2025', client: '', location: 'Vienna', website: '', services: ['Personal'], credits: [], featured_home: false, focal: '50% 20%', image: S('photos-1787300225660-8itm.avif'), content: '', gallery: gal('photos-1787300225660-8itm.avif'), ogImage: '' },
	{ slug: 'untitled', title: 'Untitled', category: 'Portrait', year: '2024', client: '', location: 'Vienna', website: '', services: ['Portrait'], credits: [], featured_home: false, focal: '50% 20%', image: S('photos-1787300237173-kdba.avif'), content: '', gallery: gal('photos-1787300237173-kdba.avif', 'photos-1787300281605-uqp5.avif'), ogImage: '' },
];

function decode(s: string): string {
	return (s || '')
		.replace(/<[^>]+>/g, '')
		.replace(/&#0?38;|&amp;/g, '&')
		.replace(/&#8211;|&#8212;/g, '—')
		.replace(/&#8216;|&#8217;/g, '’')
		.replace(/&#8220;|&#8221;/g, '"')
		.replace(/&#(\d+);/g, (_m, n) => String.fromCharCode(+n))
		.trim();
}

/** Build a responsive `srcset` string from a WordPress media_details.sizes map.
 *  WordPress generates several widths per upload; exposing them lets the browser
 *  fetch the smallest file that fits, which is a real Core-Web-Vitals win.
 *  Dedupes by width and returns '' when only the original size exists. */
function srcsetFromSizes(md: any): string {
	const sizes = md && md.sizes;
	if (!sizes || typeof sizes !== 'object') return '';
	const byW = new Map<number, string>();
	for (const k of Object.keys(sizes)) {
		const s = sizes[k];
		const w = +(s && s.width) || 0;
		const url = s && s.source_url;
		if (w && url && !byW.has(w)) byW.set(w, url);
	}
	// Include the full-size original too, so the largest candidate is present.
	const fullW = +md.width || 0;
	if (fullW && md.source_url && !byW.has(fullW)) byW.set(fullW, md.source_url);
	if (byW.size < 2) return '';
	return [...byW.entries()].sort((a, b) => a[0] - b[0]).map(([w, u]) => `${u} ${w}w`).join(', ');
}

function mapProject(p: any): Project {
	let image = '', imageW = 0, imageH = 0, imageSrcset = '';
	try {
		const fm = p._embedded['wp:featuredmedia'][0];
		image = fm.source_url || '';
		const md = fm.media_details || {};
		imageW = +md.width || 0; imageH = +md.height || 0;
		imageSrcset = srcsetFromSizes({ ...md, source_url: image });
	} catch {}
	let category = '';
	try {
		const terms = ([] as any[]).concat(...(p._embedded['wp:term'] || []));
		const t = terms.find((x) => x && x.taxonomy === 'work_category');
		category = t ? t.name : '';
	} catch {}
	const f = (p.project || {}) as any;
	const gallery: GalleryImage[] = Array.isArray(p.gallery)
		? p.gallery
				.filter((g: any) => g && g.src)
				.map((g: any) => ({ src: g.src, w: +g.w || 0, h: +g.h || 0, srcset: g.srcset || '' }))
		: [];
	const credits: Credit[] = Array.isArray(f.credits)
		? f.credits.map((c: any) => ({ role: c.role || '', name: c.name || '', url: c.url || '' }))
		: [];
	const services: string[] = Array.isArray(f.services) ? f.services.filter(Boolean) : [];
	const series: Term[] = Array.isArray(f.series)
		? f.series.map((t: any) => ({ name: t.name || '', slug: t.slug || '' })).filter((t: Term) => t.slug)
		: [];
	const seo = (p.seo || {}) as any;
	return {
		slug: p.slug,
		title: decode(p.title && p.title.rendered),
		category: category || '',
		year: String(f.year || (p.date || '').slice(0, 4) || ''),
		client: f.client || '',
		location: f.location || '',
		website: f.website || '',
		services,
		series,
		credits,
		featured_home: !!f.featured_home,
		focal: (typeof f.focal === 'string' && f.focal.trim()) ? f.focal : '50% 20%',
		image,
		imageW,
		imageH,
		imageSrcset,
		content: (p.content && p.content.rendered) || '',
		gallery,
		ogImage: seo.og_image || image || '',
	};
}

async function fetchJson(path: string): Promise<any[] | null> {
	try {
		const res = await fetch(WP_BASE + path);
		if (!res.ok) throw new Error('HTTP ' + res.status);
		const data = await res.json();
		return Array.isArray(data) ? data : null;
	} catch (e) {
		console.warn('[wp] falling back to sample data — API unreachable:', (e as Error).message);
		return null;
	}
}

export async function getProjects(): Promise<Project[]> {
	const data = await fetchJson('/work?per_page=100&_embed&orderby=menu_order&order=asc');
	if (!data || !data.length) return SAMPLE;
	return data.map(mapProject);
}

export async function getProject(slug: string): Promise<Project | undefined> {
	const data = await fetchJson('/work?slug=' + encodeURIComponent(slug) + '&_embed');
	if (data && data.length) return mapProject(data[0]);
	return SAMPLE.find((p) => p.slug === slug);
}

/* ── Journal (native WordPress posts) ── */
export interface Post {
	slug: string;
	title: string;
	excerpt: string;
	content: string;
	date: string;       // ISO
	dateLabel: string;  // e.g. "12 August 2026"
	category: string;
	image: string;
	imageW?: number;
	imageH?: number;
	imageSrcset?: string;
}

const SAMPLE_POSTS: Post[] = [
	{ slug: 'available-light', title: 'On available light', excerpt: 'Why I keep the flash in the bag, and what the window already knows.', content: '<p>Notes on working with what the room gives you.</p>', date: '2026-06-14', dateLabel: '14 June 2026', category: 'Craft', image: '' },
	{ slug: 'a-year-in-albums', title: 'A year, kept in albums', excerpt: 'Looking back on twelve months of portraits, weddings and quiet streets.', content: '<p>A short retrospective.</p>', date: '2026-01-04', dateLabel: '4 January 2026', category: 'Journal', image: '' },
	{ slug: 'tfp-and-trust', title: 'TFP, and the value of trust', excerpt: 'How collaborative sessions became the backbone of the studio.', content: '<p>On working for the picture, not the invoice.</p>', date: '2025-11-20', dateLabel: '20 November 2025', category: 'Journal', image: '' },
];

function fmtDate(iso: string): string {
	try { return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }); }
	catch { return (iso || '').slice(0, 10); }
}

function mapPost(p: any): Post {
	let image = '', imageW = 0, imageH = 0, imageSrcset = '';
	try {
		const fm = p._embedded['wp:featuredmedia'][0];
		image = fm.source_url || '';
		const md = fm.media_details || {};
		imageW = +md.width || 0; imageH = +md.height || 0;
		imageSrcset = srcsetFromSizes({ ...md, source_url: image });
	} catch {}
	let category = '';
	try {
		const terms = ([] as any[]).concat(...(p._embedded['wp:term'] || []));
		const t = terms.find((x) => x && x.taxonomy === 'category');
		category = t ? t.name : '';
	} catch {}
	return {
		slug: p.slug,
		title: decode(p.title && p.title.rendered),
		excerpt: decode(p.excerpt && p.excerpt.rendered),
		content: (p.content && p.content.rendered) || '',
		date: p.date || '',
		dateLabel: fmtDate(p.date || ''),
		category: category || 'Journal',
		image, imageW, imageH, imageSrcset,
	};
}

export async function getPosts(): Promise<Post[]> {
	const data = await fetchJson('/posts?per_page=50&_embed');
	if (!data || !data.length) return SAMPLE_POSTS;
	return data.map(mapPost);
}

export async function getPost(slug: string): Promise<Post | undefined> {
	const data = await fetchJson('/posts?slug=' + encodeURIComponent(slug) + '&_embed');
	if (data && data.length) return mapPost(data[0]);
	return SAMPLE_POSTS.find((p) => p.slug === slug);
}

/* ── Site settings (editable in WordPress → Site settings) ── */
export interface SiteStat { label: string; value: string }
export interface PriceType { label: string; base: number }
export interface AddOn { label: string; price: number }
export interface Session { name: string; base: number; hrs: number; extra: number; note: string }
export interface SpecRow { label: string; value: string }
export interface Honor { year: string; title: string; tag: string }
export interface Testimonial { text: string; by: string }
export interface Faq { q: string; a: string }
export interface Site {
	studio: { lede: string; bio: string; portrait: string; statement: string[]; spec: SpecRow[]; stats: SiteStat[]; clients: string[] };
	home: { variant: string; hero_lines: string[]; hero_lede: string; marquee: string; honors: Honor[]; testimonials: Testimonial[] };
	contact: { email: string; location: string; response: string; instagram: string; behance: string; linkedin: string };
	pricing: { currency: string; types: PriceType[]; addons: AddOn[]; sessions: Session[]; licence: number; per_km: number };
	faq: Faq[];
	security: { turnstile_site: string };
}

const SITE_FALLBACK: Site = {
	studio: {
		lede: 'A practice of looking slowly — and keeping only what lasts.',
		bio: '<p>Nishuthan Raveenthiran is a photographer based in Vienna, working across Europe on portrait, wedding and street photography. The work favours available light, restraint, and the quiet spaces between moments.</p><p>Much of it begins as personal and collaborative work — TFP sessions with people who trust the process — and grows into commissions from there. Each project is kept as an album; the archive lives under Work.</p><p>For commissions and personal projects, start an enquiry — an instant estimate, with a firm quote by email within 24 hours.</p>',
		portrait: '',
		statement: ['NO PRESETS.', 'NO SHORTCUTS.', 'JUST THE FRAME.'],
		spec: [
			{ label: 'FOCUS', value: 'PORTRAIT · FASHION · EVENT · WEDDING' },
			{ label: 'BASE', value: 'VIENNA, AUSTRIA' },
			{ label: 'SINCE', value: 'MMXX' },
			{ label: 'LANGUAGES', value: 'EN · DE · TA' },
		],
		stats: [
			{ label: 'Projects', value: '120+' }, { label: 'Countries', value: '14' },
			{ label: 'Publications', value: '30+' }, { label: 'Based in', value: 'Vienna, AT' },
		],
		clients: ['SZ Magazin', 'Apartamento', 'NYT Magazine', 'Belvedere', 'Hotel Sacher'],
	},
	home: {
		variant: 'raveenthiran',
		hero_lines: ['PHOTOGRAPHS', 'THAT OUTLIVE', 'TRENDS.'],
		hero_lede: 'Raveenthiran photographs people — portrait, fashion, event, wedding and everything between — in Vienna and across Austria. Prepared like a production, delivered like a promise.',
		marquee: 'RAVEENTHIRAN — VIENNA — PORTRAIT — FASHION — EVENT — WEDDING — ',
		honors: [
			{ year: '2026', title: 'INTERNATIONAL PORTRAIT AWARDS', tag: 'SHORTLIST' },
			{ year: '2025', title: 'FOTO WIEN — GROUP SHOW', tag: 'EXHIBITION' },
			{ year: '2025', title: 'VIENNA PHOTO FESTIVAL', tag: 'FEATURE' },
			{ year: '2024', title: 'AUSTRIAN WEDDING AWARDS', tag: 'WINNER — REPORTAGE' },
		],
		testimonials: [
			{ text: 'He directed the whole shoot so calmly that I forgot the camera was there. The prints hang in our living room.', by: 'SARAH K. — PORTRAIT, 2024' },
			{ text: 'Booked two hours, kept the gallery for life. The KHM series is my favourite thing on my wall.', by: 'MIRI — FASHION, 2026' },
			{ text: 'The only photographer who delivered earlier than promised. Every single frame usable.', by: 'LUNA M. — STREET PORTRAIT, 2025' },
		],
	},
	contact: { email: 'hello@raveenthiran.com', location: 'Vienna, AT', response: 'Within 24 hours', instagram: '', behance: '', linkedin: '' },
	pricing: {
		currency: '€',
		types: [ { label: 'Portrait', base: 450 }, { label: 'Wedding', base: 1800 }, { label: 'Event', base: 900 }, { label: 'Editorial', base: 1200 } ],
		addons: [ { label: 'Advanced retouching', price: 180 }, { label: 'Express delivery', price: 240 }, { label: 'Second shooter', price: 300 }, { label: 'Hair & makeup', price: 350 } ],
		sessions: [
			{ name: 'PORTRAIT', base: 150, hrs: 2, extra: 120, note: '' },
			{ name: 'EVENT', base: 690, hrs: 3, extra: 120, note: '' },
			{ name: 'WEDDING', base: 1200, hrs: 5, extra: 220, note: '' },
			{ name: 'FASHION / COMMERCIAL', base: 220, hrs: 2, extra: 120, note: 'Price excludes a commercial usage license — licensing is quoted per campaign.' },
		],
		licence: 150, per_km: 0.42,
	},
	faq: [
		{ q: 'How do we get started?', a: 'Send an enquiry with a few details about your project. I reply within 24 hours with availability and a firm quote.' },
		{ q: 'Is the estimate final?', a: 'It is indicative — a fast way to gauge scope. The final quote is confirmed by email once we have talked through the details.' },
		{ q: 'Do you travel?', a: 'Yes. I am based in Vienna and work across Europe. Travel beyond the city is added per kilometre.' },
		{ q: 'When do I receive the images?', a: 'Edited galleries are delivered within two to three weeks. Express delivery is available as an add-on.' },
	],
	security: { turnstile_site: '' },
};

function pick<T>(v: T | undefined | null | '', fb: T): T { return (v === undefined || v === null || v === '' ) ? fb : v; }
function arr<T>(v: T[] | undefined | null, fb: T[]): T[] { return Array.isArray(v) && v.length ? v : fb; }

export async function getSite(): Promise<Site> {
	const base = WP_BASE.replace(/\/wp\/v2\/?$/, '');
	try {
		const r = await fetch(base + '/rvn/v1/site', { mode: 'cors', credentials: 'omit' });
		if (!r.ok) throw new Error('HTTP ' + r.status);
		const d: any = await r.json();
		const F = SITE_FALLBACK;
		return {
			studio: {
				lede: pick(d?.studio?.lede, F.studio.lede),
				bio: pick(d?.studio?.bio, F.studio.bio),
				portrait: pick(d?.studio?.portrait, ''),
				statement: arr(d?.studio?.statement, F.studio.statement),
				spec: arr(d?.studio?.spec, F.studio.spec),
				stats: arr(d?.studio?.stats, F.studio.stats),
				clients: arr(d?.studio?.clients, F.studio.clients),
			},
			home: {
				variant: pick(d?.home?.variant, F.home.variant),
				hero_lines: arr(d?.home?.hero_lines, F.home.hero_lines),
				hero_lede: pick(d?.home?.hero_lede, F.home.hero_lede),
				marquee: pick(d?.home?.marquee, F.home.marquee),
				honors: arr(d?.home?.honors, F.home.honors),
				testimonials: arr(d?.home?.testimonials, F.home.testimonials),
			},
			contact: {
				email: pick(d?.contact?.email, F.contact.email),
				location: pick(d?.contact?.location, F.contact.location),
				response: pick(d?.contact?.response, F.contact.response),
				instagram: pick(d?.contact?.instagram, ''),
				behance: pick(d?.contact?.behance, ''),
				linkedin: pick(d?.contact?.linkedin, ''),
			},
			pricing: {
				currency: pick(d?.pricing?.currency, F.pricing.currency),
				types: arr(d?.pricing?.types, F.pricing.types),
				addons: arr(d?.pricing?.addons, F.pricing.addons),
				sessions: arr(d?.pricing?.sessions, F.pricing.sessions),
				licence: pick(d?.pricing?.licence, F.pricing.licence),
				per_km: pick(d?.pricing?.per_km, F.pricing.per_km),
			},
			faq: arr(d?.faq, F.faq),
			security: { turnstile_site: pick(d?.security?.turnstile_site, '') },
		};
	} catch (e) {
		console.warn('[wp] site settings fallback —', (e as Error).message);
		return SITE_FALLBACK;
	}
}
