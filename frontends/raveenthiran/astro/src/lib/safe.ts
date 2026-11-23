/**
 * Build-time helpers for rendering WordPress (CMS) data into static pages.
 *
 * Everything here runs in Astro's SSG at build time — nothing ships to the
 * browser. The CMS is trusted, but a compromised or mistyped field should never
 * be able to break out of a <script> block or turn a link into script.
 */

/** JSON for inline <script type="application/json|ld+json"> blocks.
 *  JSON.stringify alone does not escape "</script>" or "<!--", so CMS text
 *  containing them could end the block early. The escapes below are valid JSON
 *  and parse back to exactly the same value. */
export function jsonForScript(value: unknown): string {
	return JSON.stringify(value ?? null)
		.replace(/</g, '\\u003c')
		.replace(/>/g, '\\u003e')
		.replace(/&/g, '\\u0026')
		.replace(/\u2028/g, '\\u2028')
		.replace(/\u2029/g, '\\u2029');
}

/** A link target from the CMS, or undefined when it is not safe to render.
 *  Allows http(s), mailto and same-site relative paths ("/work/…"). A bare
 *  domain typed without a scheme ("example.com/page") is upgraded to https. */
export function safeUrl(u: unknown): string | undefined {
	if (typeof u !== 'string') return undefined;
	const s = u.trim();
	if (!s) return undefined;
	if (s.startsWith('/') && !s.startsWith('//')) return s;
	if (/^[a-z0-9][a-z0-9.-]*\.[a-z]{2,}(?:[/?#:]|$)/i.test(s)) return safeUrl('https://' + s);
	try {
		const url = new URL(s);
		return ['http:', 'https:', 'mailto:'].includes(url.protocol) ? url.href : undefined;
	} catch {
		return undefined;
	}
}

const ON_ATTR = /\s+on[a-z]+\s*=\s*(?:"[^"]*"|'[^']*'|[^\s>]+)/gi;
const BAD_URL = /(\s(?:href|src|xlink:href|action|formaction|poster|data)\s*=\s*)(["']?)\s*(?:javascript|vbscript|data:text\/html)[^"'\s>]*/gi;

/** BASIC defence-in-depth for rendered WordPress HTML (post/project bodies,
 *  FAQ answers): removes <script> elements, inline on*="" event handlers and
 *  javascript:/vbscript: URLs. Embeds (iframes), styles and shortcode markup
 *  are kept so existing content renders unchanged. This is not a full
 *  sanitiser — WordPress' own kses filtering stays the primary control. */
export function cleanHtml(html: unknown): string {
	if (typeof html !== 'string' || !html) return '';
	return html
		.replace(/<script\b[\s\S]*?<\/script\s*>/gi, '')
		.replace(/<script\b[^>]*>/gi, '')
		.replace(/<[a-z][^>]*>/gi, (tag) => tag.replace(ON_ATTR, '').replace(BAD_URL, '$1$2#'));
}
