import { defineConfig } from 'astro/config';

// Static (SSG) build. WordPress is queried only at build time (see src/lib/wp.ts),
// so the output in dist/ is fully static — fast, and it runs even when the NAS
// is offline. Deploy the contents of dist/ to the easyname web root.
export default defineConfig({
	site: 'https://raveenthiran.com',
	output: 'static',
	build: { format: 'directory' }, // /work/ -> /work/index.html
});
