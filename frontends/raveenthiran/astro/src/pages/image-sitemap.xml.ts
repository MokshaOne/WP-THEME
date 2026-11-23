import type { APIRoute } from 'astro';
import { getProjects } from '../lib/wp';

// Google image sitemap: lists every Work project page with the photographs it
// contains, so image search can index the portfolio. Complements the regular
// page sitemap (referenced from robots.txt). Built statically at build time.
export const GET: APIRoute = async ({ site }) => {
	const base = (site?.href || 'https://raveenthiran.com/').replace(/\/$/, '');
	const projects = await getProjects();
	const esc = (s: string) =>
		(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	const abs = (u: string) => (/^https?:\/\//.test(u) ? u : base + (u.startsWith('/') ? '' : '/') + u);

	const urls = projects
		.map((p) => {
			const imgs = [p.image, ...p.gallery.map((g) => g.src)].filter(Boolean);
			const seen = new Set<string>();
			const uniq = imgs.filter((u) => (seen.has(u) ? false : (seen.add(u), true)));
			if (!uniq.length) return '';
			const loc = `${base}/work/${p.slug}/`;
			const caption = esc([p.title, p.category, p.location, p.year].filter(Boolean).join(' — '));
			const images = uniq
				.map(
					(u) =>
						`    <image:image><image:loc>${esc(abs(u))}</image:loc>` +
						(caption ? `<image:caption>${caption}</image:caption>` : '') +
						`<image:title>${esc(p.title)}</image:title></image:image>`,
				)
				.join('\n');
			return `  <url>\n    <loc>${loc}</loc>\n${images}\n  </url>`;
		})
		.filter(Boolean)
		.join('\n');

	const xml = `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">\n${urls}\n</urlset>\n`;

	return new Response(xml, {
		headers: { 'Content-Type': 'application/xml; charset=utf-8' },
	});
};
