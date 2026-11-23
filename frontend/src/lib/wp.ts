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

export interface Project {
	slug: string;
	title: string;
	category: string;
	year: string;
	client: string;
	role: string;
	location: string;
	website: string;
	image: string;            // featured image URL ('' if none)
	content: string;          // rendered HTML body ('' if none)
	gallery: GalleryImage[];  // additional images attached to the project
}

/* ── offline / first-run fallback ── */
const SAMPLE: Project[] = [
	{ slug: 'nachtdienst',           title: 'Nachtdienst',           category: 'Editorial',    year: '2024', client: 'SZ Magazin',   role: 'Photography', location: 'Wien', website: '', image: '', content: '', gallery: [] },
	{ slug: 'a-house-in-mariahilf',  title: 'A House in Mariahilf',  category: 'Editorial',    year: '2024', client: 'Apartamento',  role: 'Photography', location: 'Wien', website: '', image: '', content: '', gallery: [] },
	{ slug: 'the-vienna-notebook',   title: 'The Vienna Notebook',   category: 'Portrait',     year: '2024', client: 'NYT Magazine', role: 'Photography', location: 'Wien', website: '', image: '', content: '', gallery: [] },
	{ slug: 'naschmarkt-at-dawn',    title: 'Naschmarkt at Dawn',    category: 'Street',       year: '2023', client: 'Personal',     role: 'Photography', location: 'Wien', website: '', image: '', content: '', gallery: [] },
	{ slug: 'belvedere-after-hours', title: 'Belvedere After Hours', category: 'Architecture', year: '2023', client: 'Belvedere',    role: 'Photography', location: 'Wien', website: '', image: '', content: '', gallery: [] },
	{ slug: 'rote-bar',              title: 'Rote Bar',              category: 'Event',        year: '2023', client: 'Hotel Sacher', role: 'Photography', location: 'Wien', website: '', image: '', content: '', gallery: [] },
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

function mapProject(p: any): Project {
	let image = '';
	try { image = p._embedded['wp:featuredmedia'][0].source_url || ''; } catch {}
	let category = '';
	try {
		const terms = ([] as any[]).concat(...(p._embedded['wp:term'] || []));
		const t = terms.find((x) => x && x.taxonomy === 'work_category');
		category = t ? t.name : '';
	} catch {}
	const f = (p.project || {}) as Record<string, string>;
	const gallery: GalleryImage[] = Array.isArray(p.gallery)
		? p.gallery
				.filter((g: any) => g && g.src)
				.map((g: any) => ({ src: g.src, w: +g.w || 0, h: +g.h || 0, srcset: g.srcset || '' }))
		: [];
	return {
		slug: p.slug,
		title: decode(p.title && p.title.rendered),
		category: category || '',
		year: f.year || (p.date || '').slice(0, 4),
		client: f.client || '',
		role: f.role || '',
		location: f.location || '',
		website: f.website || '',
		image,
		content: (p.content && p.content.rendered) || '',
		gallery,
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
