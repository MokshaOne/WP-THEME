=== Direction Manual ===
Contributors: on1agency
Tags: photography, guide, manual, shortcode, embed
Requires at least: 5.6
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Embed the 108-volume photography Direction Manual anywhere on your site, and choose exactly where it shows.

== Description ==

Direction Manual bundles a complete, self-contained photography direction manual (108 volumes: studio, lighting technique, portrait, boudoir, product, food, outdoor, creative, business, and public / events / weddings) and lets you place it wherever you want.

The manual is rendered inside an isolated iframe, so its styles and scripts never clash with your theme. It is camera- and location-agnostic and works in light and dark modes.

**Ways to show it (use any combination):**

* **Shortcode** — add `[direction_manual]` to any page, post, or widget. Override height with `[direction_manual height="900px"]`.
* **Block** — insert the "Direction Manual" block in the block editor.
* **Auto page** — in Settings → Direction Manual, choose a page and have the manual appended to, or replace, that page's content.
* **Standalone URL** — enable a full-screen page at `/direction-manual/` (the slug is configurable).

== Installation ==

1. Upload the `direction-manual` folder to `/wp-content/plugins/`, or install the ZIP via Plugins → Add New → Upload Plugin.
2. Activate the plugin.
3. Go to **Settings → Direction Manual** to choose where it appears.

== Frequently Asked Questions ==

= How do I put it on a specific page? =
Add the `[direction_manual]` shortcode or the "Direction Manual" block to that page, or select the page under Settings → Direction Manual and choose Append or Replace.

= Can I control the height? =
Yes — set a CSS length (e.g. `85vh`, `900px`, `100%`) in the settings, or per-shortcode with the `height` attribute.

= Does it slow down my site? =
The manual loads in an iframe with `loading="lazy"`, so it only loads when scrolled into view.

== Changelog ==

= 1.0.0 =
* Initial release: shortcode, block, page append/replace, and standalone URL.
