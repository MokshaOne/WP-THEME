// Build manifest for the OG-card generator (scripts/og.mjs): one record per
// project. Not linked from anywhere; it ships with the site but is harmless
// (it holds only data that is public on the pages themselves).
import { getProjects } from '../lib/wp';

export async function GET() {
	const projects = await getProjects();
	return new Response(
		JSON.stringify(
			projects.map((p) => ({
				slug: p.slug,
				title: p.title,
				category: p.category,
				year: p.year,
				image: p.image,
				focal: p.focal,
			})),
		),
		{ headers: { 'Content-Type': 'application/json' } },
	);
}
