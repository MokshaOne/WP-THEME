# Translations

The theme loads this folder via `load_theme_textdomain( 'raveenthiran', .../languages )`.

- `raveenthiran.pot` — starter template. Regenerate the complete catalogue with
  WP-CLI: `wp i18n make-pot . languages/raveenthiran.pot`
- Add a locale by creating `raveenthiran-<locale>.po` (e.g. `raveenthiran-de_DE.po`),
  translating it, and compiling to `.mo` (`wp i18n make-mo languages`).

The site is English-only today; this scaffolding makes it translatable later
without code changes.
