// Presentation helpers for the editorial design (v5). Pure functions, no data
// access — the WordPress layer in wp.ts stays untouched.

/** Zero-padded index, e.g. pad(3) → "03", pad(3, 3) → "003". */
export const pad = (n: number, len = 2) => String(n).padStart(len, '0');

/**
 * CMS copy written for the old all-caps poster design ("NO PRESETS.") reads as
 * shouting in sentence-set serif type. Strings without any lowercase letter are
 * turned into sentence case; mixed-case strings are returned unchanged, so an
 * editor who writes proper case in WordPress keeps full control.
 */
export function soften(s: string): string {
	const t = (s || '').trim();
	if (!t || /[a-zäöüß]/.test(t)) return t;
	const lower = t.toLocaleLowerCase('en');
	return lower.charAt(0).toLocaleUpperCase('en') + lower.slice(1);
}

/** Short caption "Portrait · Vienna · 2025" from project metadata. */
export const caption = (...parts: (string | undefined)[]) => parts.map((p) => soften(p || '')).filter(Boolean).join(' · ');
