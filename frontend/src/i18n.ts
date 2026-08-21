/**
 * UI-string dictionary for the DE/EN interface switch. WordPress-authored
 * content (project titles, bios, FAQ answers) is shown as written; this
 * covers the site chrome. Elements are tagged with data-i18n="key" and the
 * dictionary is applied client-side (see initI18n in ui.js). English is the
 * rendered default, so it is also the graceful no-JS fallback.
 */
export const dict: Record<string, Record<string, string>> = {
	en: {
		'nav.work': 'Work', 'nav.journal': 'Journal', 'nav.studio': 'Studio', 'nav.enquire': 'Enquire',
		'ovl.welcome': 'A photography practice in Vienna, working across Europe.',
		'ovl.albums': 'Albums',
		'foot.sub_or': 'or', 'foot.sub_enquiry': 'start an enquiry', 'foot.sub_tail': '— a firm quote within 24 hours.',
		'foot.studio': 'Studio', 'foot.recent': 'Recent work', 'foot.albums': 'Albums',
		'home.eyebrow': 'Vienna · Photography', 'home.selected': 'Selected work', 'home.allwork': 'All work →',
		'home.scroll': 'Scroll', 'home.view': 'View project →', 'home.prev': '← Prev', 'home.next': 'Next →',
		'work.eyebrow_tail': 'projects · Vienna', 'work.title': 'Selected Work', 'work.all': 'All', 'work.grid': 'Grid', 'work.index': 'Index', 'work.empty': 'Nothing in this album yet.',
		'studio.title': 'The Studio', 'studio.clients': 'Selected clients',
		'proj.next': 'Next project', 'proj.allwork': '← All work', 'proj.enquire': 'Enquire about a shoot →',
		'enq.eyebrow': 'Enquire · Vienna', 'enq.title': 'Start a project',
		'enq.lead': 'Tell me what you have in mind. Get an instant estimate — a firm quote follows by email within 24 hours.',
		'enq.step1': '01 · Project type', 'enq.step2': '02 · Add-ons', 'enq.step3': '03 · Your details',
		'enq.name': 'Name', 'enq.email': 'Email', 'enq.date': 'Preferred date', 'enq.notes': 'Tell me about it',
		'enq.licence': 'Commercial licence', 'enq.travel': 'Travel (km)', 'enq.send': 'Send enquiry →',
		'enq.estimate': 'Estimate', 'enq.questions': 'Questions', 'enq.response_k': 'Response',
		'journal.eyebrow': 'Notes & stories', 'journal.title': 'Journal', 'journal.read': 'Read →',
		'journal.next': 'Next entry', 'journal.all': '← All entries',
		'nf.eyebrow': 'Error 404', 'nf.title': 'This frame is empty.',
		'nf.lead': 'The page you were looking for isn’t here — it may have moved, or never existed.',
		'nf.home': 'Back home', 'nf.browse': 'Browse the work →',
	},
	de: {
		'nav.work': 'Arbeiten', 'nav.journal': 'Journal', 'nav.studio': 'Studio', 'nav.enquire': 'Anfrage',
		'ovl.welcome': 'Ein Fotografie-Atelier in Wien, tätig in ganz Europa.',
		'ovl.albums': 'Alben',
		'foot.sub_or': 'oder', 'foot.sub_enquiry': 'eine Anfrage stellen', 'foot.sub_tail': '— ein verbindliches Angebot binnen 24 Stunden.',
		'foot.studio': 'Atelier', 'foot.recent': 'Neueste Arbeiten', 'foot.albums': 'Alben',
		'home.eyebrow': 'Wien · Fotografie', 'home.selected': 'Ausgewählte Arbeiten', 'home.allwork': 'Alle Arbeiten →',
		'home.scroll': 'Scrollen', 'home.view': 'Projekt ansehen →', 'home.prev': '← Zurück', 'home.next': 'Weiter →',
		'work.eyebrow_tail': 'Projekte · Wien', 'work.title': 'Ausgewählte Arbeiten', 'work.all': 'Alle', 'work.grid': 'Raster', 'work.index': 'Index', 'work.empty': 'In diesem Album ist noch nichts.',
		'studio.title': 'Das Atelier', 'studio.clients': 'Ausgewählte Kunden',
		'proj.next': 'Nächstes Projekt', 'proj.allwork': '← Alle Arbeiten', 'proj.enquire': 'Shooting anfragen →',
		'enq.eyebrow': 'Anfrage · Wien', 'enq.title': 'Projekt starten',
		'enq.lead': 'Erzähl mir, was dir vorschwebt. Du bekommst sofort eine Schätzung — ein verbindliches Angebot folgt per E-Mail binnen 24 Stunden.',
		'enq.step1': '01 · Art des Projekts', 'enq.step2': '02 · Zusatzoptionen', 'enq.step3': '03 · Deine Angaben',
		'enq.name': 'Name', 'enq.email': 'E-Mail', 'enq.date': 'Wunschtermin', 'enq.notes': 'Erzähl davon',
		'enq.licence': 'Kommerzielle Lizenz', 'enq.travel': 'Anfahrt (km)', 'enq.send': 'Anfrage senden →',
		'enq.estimate': 'Schätzung', 'enq.questions': 'Fragen', 'enq.response_k': 'Antwort',
		'journal.eyebrow': 'Notizen & Geschichten', 'journal.title': 'Journal', 'journal.read': 'Lesen →',
		'journal.next': 'Nächster Eintrag', 'journal.all': '← Alle Einträge',
		'nf.eyebrow': 'Fehler 404', 'nf.title': 'Dieser Rahmen ist leer.',
		'nf.lead': 'Die gesuchte Seite ist nicht hier — vielleicht wurde sie verschoben oder existierte nie.',
		'nf.home': 'Zur Startseite', 'nf.browse': 'Arbeiten ansehen →',
	},
};
