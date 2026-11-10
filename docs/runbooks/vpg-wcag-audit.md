# VPG · WCAG-2.2-AA-Audit — Checkliste

**Code-Pass erledigt** (Theme): globales `:focus-visible` (roter Outline),
`prefers-reduced-motion` stoppt Ticker/Reveals/Transitions, Skip-Link
vorhanden, Formulare mit echten Labels, Lightbox als `role=dialog
aria-modal` mit Esc/Pfeiltasten, Touch-Targets ≥ 44 px, Alt-Text-Pipeline
(KI-Integration als Fallback). Lighthouse-CI hält a11y ≥ 0.9.

**Was nur ein manueller Durchgang klären kann** — pro Release einmal:

## 1 · Tastatur (ohne Maus, 15 min)

- [ ] Tab-Reihenfolge Home → Join → Formular absenden logisch, kein Trap
- [ ] Map: Zoom-Controls und Popups per Tastatur erreichbar (Leaflet ist
      es; unsere Custom-Buttons ⤢/◎ testen)
- [ ] Lightbox: Fokus bleibt im Dialog, Esc schließt, Fokus kehrt zurück
- [ ] Mobile-Nav (Checkbox-Hack!): per Tastatur öffenbar? Bekannte
      Schwäche des CSS-only-Patterns → falls nein: `tabindex` +
      Enter-Handler auf das Label nachrüsten (10 Zeilen, main.js)

## 2 · Screenreader (NVDA free / VoiceOver, 30 min)

- [ ] Landmark-Struktur: banner / main / contentinfo werden angesagt
- [ ] Karten-Popups: Inhalt erreichbar oder gleichwertige Liste darunter
      (Archiv-Grid existiert → gleichwertig, gut)
- [ ] Formular-Fehler werden vorgelesen (Toast ist visuell —
      `role=status` auf `.vpg-toast` ergänzen, 1 Zeile)
- [ ] Ticker/dekoratives als `aria-hidden` (Prototyp hatte es; live prüfen)

## 3 · Visuell

- [ ] Kontrast: `#6A6A6A` auf Weiß = 5,7:1 ✓ · `#9C9A95` auf Weiß = 2,8:1
      → **nur für dekorative/redundante Texte zulässig**, nie für
      alleinstehende Information (bei Verstößen auf `#6A6A6A` heben)
- [ ] 200 % Browser-Zoom: kein horizontales Scrollen, Sticky-Bars stapeln
      nicht über Inhalt
- [ ] Fokus-Ring auf dunklen Sektionen sichtbar (rot auf Schwarz ok)

Befunde als Issues anlegen, mit `a11y`-Label; ein Durchgang ohne rote
Punkte = AA-ready für die Selbsterklärung.
