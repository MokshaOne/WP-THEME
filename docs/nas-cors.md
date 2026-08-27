# CORS für die Medien — damit der WebGL-Hover-Effekt live läuft

Der WebGL-Hover-Effekt (Ripple + RGB-Split auf den Bildern) lädt für die GPU
eine **eigene, CORS-fähige Kopie** des Bildes. Solange Frontend und Bilder von
**derselben** Domain kommen, funktioniert das ohne Zutun. Da die Bilder aber von
`wp.m1o.at` (NAS) kommen und die Seite auf `raveenthiran.com` läuft, ist das
**cross-origin** — dafür muss der NAS beim Ausliefern der Uploads einen
CORS-Header senden.

> **Ohne diese Einstellung passiert nichts Schlimmes:** der Effekt fällt einfach
> lautlos auf das normale Bild zurück. Die Seite bleibt in jedem Fall heil — der
> Header schaltet den Effekt nur *zusätzlich* frei.

Erlaube als Origin **`https://raveenthiran.com`** (nicht `*`, das ist enger und
sauberer). Wähle die Variante, die zu deinem Webserver passt.

---

## Apache (`.htaccess`)

Lege diese Datei in `…/wp-content/uploads/.htaccess` ab (oder ergänze die
bestehende):

```apache
<IfModule mod_headers.c>
  <FilesMatch "\.(avif|webp|jpe?g|png|gif|svg)$">
    Header set Access-Control-Allow-Origin "https://raveenthiran.com"
    Header set Vary "Origin"
  </FilesMatch>
</IfModule>
```

`mod_headers` muss aktiv sein (`a2enmod headers` bzw. bei Synology-Apache i. d. R.
schon an).

---

## nginx

In den Server-/Location-Block, der `/wp-content/uploads` ausliefert:

```nginx
location ~* ^/wp-content/uploads/.+\.(avif|webp|jpe?g|png|gif|svg)$ {
    add_header Access-Control-Allow-Origin "https://raveenthiran.com" always;
    add_header Vary "Origin" always;
}
```

Danach `nginx -t && systemctl reload nginx` (bzw. den Dienst im NAS neu laden).

---

## Cloudflare Tunnel davor?

Wenn `wp.m1o.at` durch einen Cloudflare-Tunnel läuft, reicht der Origin-Header
oben — Cloudflare reicht ihn durch. Alternativ ginge eine
**Transform Rule → Response Header → Set `Access-Control-Allow-Origin`** auf
`wp.m1o.at/wp-content/uploads/*`. Nur *eine* der beiden Stellen setzen, nicht
beide (sonst doppelter Header).

---

## Prüfen

```bash
curl -I -H "Origin: https://raveenthiran.com" \
  https://wp.m1o.at/wp-content/uploads/2026/…/EIN-BILD.avif
```

In der Antwort muss stehen:

```
access-control-allow-origin: https://raveenthiran.com
```

Danach zeigt ein Hover über ein Bild auf `/work/` oder der Startseite den
Ripple-/RGB-Effekt. (Hartes Neu-Laden nicht vergessen — Cache.)
