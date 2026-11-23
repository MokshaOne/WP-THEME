# Avoiste Laverta — supply this locally (not committed)

The M1O v2 hero face is **Avoiste Laverta** (an Envato Elements licensed font).
Because this repository is public, the font files are **deliberately not committed**
— licensed assets shouldn't be redistributed via a public repo.

## To activate the couture hero type on your install
Drop these two files into this folder (`m1o.v2/assets/fonts/`):

- `avoiste-laverta.woff2`  ← required
- `avoiste-laverta.woff`   ← optional fallback

The theme detects them automatically (`functions.php` → the `nr-avoiste` block):
it declares the `@font-face`, preloads the woff2, and the homepage hero + page
titles switch to Avoiste via the `--ff-hero` CSS variable.

**If the files are absent, nothing breaks** — the hero simply falls back to Syne,
with no 404s. So the theme is safe to run without them.
