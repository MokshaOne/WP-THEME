# VPG · Caching-Runbook (Cloudflare + Shared Hosting)

Reproduzierbare Cache-Konfiguration für viennaphotogroup.com. Ziel: statische
Assets ein Jahr aus dem Edge-Cache, HTML kurz gecacht für Gäste, nie gecacht
für eingeloggte Members.

## 1 · Cloudflare Cache Rules (Dashboard → Caching → Cache Rules)

Reihenfolge ist relevant — die erste zutreffende Regel gewinnt.

| # | Regel | Ausdruck | Aktion |
|---|---|---|---|
| 1 | **Bypass eingeloggt** | Cookie enthält `wordpress_logged_in` | Bypass cache |
| 2 | **Bypass Admin/System** | URI Path beginnt mit `/wp-admin` oder `/wp-login.php` oder enthält `admin-post.php` oder `admin-ajax.php` | Bypass cache |
| 3 | **Assets lang** | URI Path endet auf `.css .js .woff2 .ttf .svg .png .jpg .jpeg .webp .avif .ics` | Eligible for cache · Edge TTL 1 Jahr · Browser TTL 1 Jahr |
| 4 | **Uploads** | URI Path beginnt mit `/wp-content/uploads/` | Eligible for cache · Edge TTL 1 Monat |
| 5 | **HTML Gäste** | Alles Übrige (Hostname = Zone) | Eligible for cache · Edge TTL 10 Minuten · Respect origin `no-cache` |

Warum sicher: Theme-Assets werden mit `filemtime`-Versionierung enqueued —
jede Änderung erzeugt eine neue URL, der Jahres-TTL kann nie stale ausliefern.

## 2 · Weitere Cloudflare-Schalter

- **Speed → Optimization**: Brotli an. Rocket Loader **aus** (bricht
  Leaflet/Inline-Scripts). Auto-Minify aus (Assets sind bereits klein,
  Minify hat schon WP-Sites zerlegt).
- **Caching → Tiered Cache**: an.
- **Always Online**: an (schadet nicht).

## 3 · Purge-Ablauf bei Releases

1. Theme-ZIP deployen (Assets bekommen neue `?ver=` durch filemtime — kein
   Purge nötig).
2. Nur bei Template-/Inhalts-Layoutänderungen: **Purge → Custom purge →
   Hostname** (nicht „Purge everything", das leert auch Fonts/Bilder).

## 4 · Origin (Shared Hosting) — .htaccess-Ergänzung

Falls der Origin ohne Cloudflare antwortet (grauer Wolken-Modus), gelten
diese Header trotzdem:

```apache
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType font/woff2            "access plus 1 year"
  ExpiresByType text/css              "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType image/webp            "access plus 1 month"
  ExpiresByType image/avif            "access plus 1 month"
  ExpiresByType image/jpeg            "access plus 1 month"
  ExpiresByType image/png             "access plus 1 month"
</IfModule>
```

## 5 · Verifizieren

```bash
# Edge-Hit prüfen (2× ausführen · zweiter Aufruf muss HIT sein)
curl -sI https://viennaphotogroup.com/wp-content/themes/vpg-v3-gallery/assets/css/gallery.css | grep -i "cf-cache-status\|cache-control"

# HTML als Gast → Edge-Cache, kurzes TTL
curl -sI https://viennaphotogroup.com/ | grep -i "cf-cache-status\|cache-control"

# Eingeloggt darf NIE aus dem Cache kommen (Cookie simulieren)
curl -sI -H "Cookie: wordpress_logged_in_x=test" https://viennaphotogroup.com/ | grep -i cf-cache-status   # → BYPASS/DYNAMIC
```

Erwartung nach Setup: `cf-cache-status: HIT` auf Assets, `DYNAMIC`/`BYPASS`
auf eingeloggtem HTML.
