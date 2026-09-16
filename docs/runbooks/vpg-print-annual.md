# VPG · Print-Annual (druckfertig, PDF/X) — Runbook

Der Jahresband braucht, was mPDF nicht kann: CMYK, Beschnitt (Bleed),
Imposition, PDF/X-Ausgabe. Werkzeug der Wahl: **Scribus** (free, open
source).

## Pipeline

1. **Inhalt exportieren** — die Issues des Jahres liegen strukturiert vor
   (`_vpg_articles`-JSON + Bilder). Export je Issue:
   `wp post meta get <issue_id> _vpg_articles` → JSON; Bilder aus
   `uploads/` in einen Jahresordner sammeln. (Ein `wp vpg annual-export
   <jahr>`-Command ist der sinnvolle nächste Ausbau des bestehenden
   `wp vpg`-CLI.)
2. **Scribus-Master** einmalig bauen: Seitenformat + 3 mm Bleed,
   Musterseiten im Gallery-Look (Archivo liegt als TTF im Theme:
   `assets/fonts/`), Absatzstile für Kicker/Titel/Body/Wall-Label.
3. **Befüllen**: Text via Story-Editor aus dem JSON, Plates als
   Bildrahmen (Originale, nicht die WebP-Ableitungen!).
4. **Ausgabe**: Datei → Exportieren → PDF: `PDF/X-3`, Farbprofil der
   Druckerei (z. B. ISO Coated v2), Beschnittzugabe an, Marken an.
5. **Preflight** kostenlos prüfen: Scribus-eigener Verifier + der
   Online-Preflight der Druckerei.

## Druckerei-Checkliste

- Auflösung: Plates ≥ 300 dpi im Endformat (2560px-Originale reichen für
  ~21 cm Breite).
- Schwarz: Text in reinem K (100/0/0/0), nicht 4c-Schwarz.
- Der EXIF-Strip der Submissions betrifft nur Web-Kopien — für den Druck
  die Originale beim Fotografen anfragen, falls mehr Auflösung nötig ist.
