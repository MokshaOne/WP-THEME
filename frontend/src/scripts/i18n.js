// Interface language toggle (EN default · DE). Content from WordPress stays in
// whatever language it was written; this only swaps the site's own chrome —
// navigation, buttons/CTAs and a few fixed labels. The choice is remembered in
// localStorage and applied on every View-Transitions navigation.
//
// To translate another string: add it to DICT (English → German) and make sure
// the element is covered by SEL below. Untranslated strings simply stay English.

const DICT = {
	// Navigation
	'WORK': 'ARBEITEN',
	'ABOUT': 'ÜBER',
	'ENQUIRY': 'ANFRAGE',
	// Buttons
	'Start a commission': 'Auftrag anfragen',
	'See the work': 'Arbeiten ansehen',
	'Browse the work →': 'Arbeiten durchsehen →',
	'Back home': 'Zur Startseite',
	'SEND ENQUIRY →': 'ANFRAGE SENDEN →',
	// CTA bands
	'BOOK THE NEXT SHOOT': 'NÄCHSTES SHOOTING BUCHEN',
	'YOUR SESSION IS NEXT': 'DU BIST ALS NÄCHSTES DRAN',
	'SEE ALL WORK': 'ALLE ARBEITEN',
	'SEE THE WORK': 'ARBEITEN ANSEHEN',
	'NEXT PROJECT': 'NÄCHSTES PROJEKT',
	'FULL INDEX →': 'GESAMTES VERZEICHNIS →',
	// Fixed labels
	'AVAILABLE FOR COMMISSIONS': 'VERFÜGBAR FÜR AUFTRÄGE',
	'Skip to content': 'Zum Inhalt springen',
};

const SEL = '.nav .lbl, .m-ovl .lbl, .btn, .cta span, .skip-link, .live';
const KEY = 'rvn-lang';

function getLang() {
	try { return localStorage.getItem(KEY) === 'de' ? 'de' : 'en'; } catch { return 'en'; }
}
function setLang(l) {
	try { localStorage.setItem(KEY, l); } catch {}
}

function apply(lang) {
	document.documentElement.lang = lang;
	document.querySelectorAll(SEL).forEach((el) => {
		// Only touch leaf text (skip elements that wrap child elements, e.g. a nav
		// <a> — we target its .lbl span instead).
		if (el.children.length) return;
		if (el.dataset.i18nEn == null) el.dataset.i18nEn = el.textContent.trim();
		const en = el.dataset.i18nEn;
		el.textContent = lang === 'de' ? (DICT[en] || en) : en;
	});
	document.querySelectorAll('[data-lang-toggle]').forEach((b) => {
		b.textContent = lang === 'de' ? 'EN' : 'DE';
		b.setAttribute('aria-pressed', lang === 'de' ? 'true' : 'false');
	});
}

function boot() {
	apply(getLang());
	document.querySelectorAll('[data-lang-toggle]').forEach((b) => {
		if (b.dataset.i18nBound) return;
		b.dataset.i18nBound = '1';
		b.addEventListener('click', () => {
			const next = getLang() === 'de' ? 'en' : 'de';
			setLang(next);
			apply(next);
		});
	});
}

document.addEventListener('astro:page-load', boot);
// First load before the router fires:
if (document.readyState !== 'loading') boot();
else document.addEventListener('DOMContentLoaded', boot);
