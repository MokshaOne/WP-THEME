# Catalogue Noir — Child Theme

Use this child theme to customise the [Catalogue Noir parent theme](../raveenthiran-catalogue-noir) without modifying parent files. When the parent gets a version bump, your changes survive.

## Install

1. Activate the parent theme first (`raveenthiran-catalogue-noir`) — required.
2. Zip this folder: `Compress-Archive -Path "raveenthiran-catalogue-noir-child" -DestinationPath "raveenthiran-catalogue-noir-child.zip"`
3. WP admin → **Appearance → Themes → Add New → Upload Theme** → choose the child zip → activate.

You should see the same site, but now Active theme is "Catalogue Noir — Child" and the parent shows as inactive parent. WordPress automatically loads parent templates when the child doesn't override them.

## How to override a template

To replace any parent template, **copy** the file from the parent theme into this child theme at the same relative path. WordPress automatically prefers the child's copy.

Example — change the single-project layout without touching the parent:

```
parent: raveenthiran-catalogue-noir/single-nr_project.php
child:  raveenthiran-catalogue-noir-child/single-nr_project.php   ← edit this copy
```

Common override targets:

| Template | Use when |
|---|---|
| `page-about.php` | Tweaking the Studio page layout |
| `single-nr_project.php` | Changing project meta rows or plate rail layout |
| `parts/booking-modal.php` | Custom LatePoint embed or extra modal copy |
| `footer.php` | Adding global tracking pixels or custom legal block |
| `parts/mobile-tabs.php` | Reordering or relabelling the bottom tab bar |

## How to override CSS

Add your CSS to `style.css` in this folder. It loads **after** the parent's `theme.css`, so equal-specificity rules win.

```css
/* style.css */
:root {
    --amber: #E83E2A;  /* override accent globally */
}

.nr-hero__title {
    font-weight: 200;  /* lighter hero titles */
}
```

## How to override PHP behavior

Use `functions.php` to register hooks. Common patterns are in this file's commented examples — uncomment + adapt.

The child's `functions.php` runs **before** the parent's, so you can `add_filter()` / `add_action()` and intercept anything the parent does.

## What NOT to do

- Don't edit parent files directly — your changes will be wiped on the next parent update.
- Don't copy the entire parent into the child — only copy templates you actually need to change.
- Don't disable the parent theme — WordPress will deactivate the child automatically and fall back to a default theme.
