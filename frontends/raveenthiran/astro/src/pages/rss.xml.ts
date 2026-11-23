import rss from '@astrojs/rss';
import type { APIContext } from 'astro';
import { getPosts } from '../lib/wp';

export async function GET(context: APIContext) {
	const posts = await getPosts();
	return rss({
		title: 'Raveenthiran — Journal',
		description: 'Notes on light, process and the work — from the Raveenthiran studio in Vienna.',
		site: context.site ?? 'https://raveenthiran.com',
		items: posts.map((p) => ({
			title: p.title,
			description: p.excerpt,
			pubDate: p.date ? new Date(p.date) : new Date(),
			link: `/journal/${p.slug}/`,
			categories: p.category ? [p.category] : undefined,
		})),
		customData: '<language>en</language>',
	});
}
