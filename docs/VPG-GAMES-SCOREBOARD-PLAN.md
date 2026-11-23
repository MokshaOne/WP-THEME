# VPG — Mobile Games + Scoreboard · Umsetzungsplan

> Ausführliche Niederschrift für das Feature **„Mobile-only Spiele mit Scoreboard"**
> im Theme `Viennaphotogroup.com/vpg-v3-gallery`.
> Status: **Planung** — noch keine Code-Umsetzung.
> Co-Produktion Raveenthiran × on1.agency.

---

## 1. Ziel & Idee

Auf der Vienna-Photo-Group-Site sollen **kleine Spiele** integriert werden, die:

1. **nur auf Handys** über ein eigenes Menü erreichbar sind (Desktop bleibt aufgeräumt),
2. **immer erreichbar** sind (feste untere Leiste im App-Stil),
3. ein **Scoreboard / Highscore-System** besitzen, das zur bestehenden
   Mitglieder-Community passt,
4. **touch-tauglich** und damit auf Smartphones nativ spielbar sind,
5. die **plugin-leichte Philosophie** des Themes respektieren (kein Game-Plugin).

Thematisch sinnvoll, weil Foto-Community: **Memory mit VPG-Fotos**, **Foto-Quiz**
(„Welcher Bezirk?", „Welcher Fotograf?"), oder ein einfaches Reaktionsspiel.

---

## 2. Architektur-Einordnung (was schon da ist)

Das Theme bringt fast alle Bausteine bereits als bewährtes Muster mit. Wir docken
nur an, statt Neues zu erfinden.

| Baustein | Vorhandene Referenz | Wiederverwendung |
|---|---|---|
| Menü-Locations | `inc/theme-setup.php` → `register_nav_menus()` (Z. 33 ff.) | neue Location `mobile-games` |
| Bedingtes Laden von CSS/JS | `inc/enqueue.php` (z. B. `is_page_template('templates/page-dashboard.php')`, Z. 53) | Games-Assets nur auf Spiel-Seiten |
| AJAX + Nonce + User-Meta | `inc/members.php:95` → `wp_ajax_vpg_toggle_bookmark`, `check_ajax_referer('vpg_bookmark')`, `update_user_meta(...)`, `wp_create_nonce('vpg_bookmark')` | **Scoreboard ist ein 1:1-Klon dieses Musters** |
| Custom Post Types | `inc/cpts.php` → `register_post_type()` | neuer CPT `vpg_game` |
| Bootstrap / Laden der Module | `functions.php` (Block „Phase-2 Extensions") | neues `inc/games.php` einhängen |
| Auto-Seiten-Erstellung | `inc/setup-wizard.php` | optional: Games-Seite automatisch anlegen |
| Gating (Mitglieder/öffentlich) | `inc/gating.php`, `inc/cpt-gating.php` | Score-Speichern auf Mitglieder beschränken |

> **Kernerkenntnis:** Der Scoreboard-Mechanismus ist technisch nahezu identisch
> mit dem bestehenden Bookmark-System. Wir kopieren ein erprobtes, sicheres Muster.

---

## 3. Bausteine im Detail

### Baustein 1 — Mobile-Only Games-Menü

**Ziel:** Ein Menü, das nur auf Smartphones sichtbar ist und als feste Leiste am
unteren Bildschirmrand „immer erreichbar" bleibt.

- **Registrierung:** neue Location `mobile-games` in `inc/theme-setup.php`
  zum bestehenden `register_nav_menus([...])`-Array hinzufügen.
- **Ausgabe:** im `footer.php` per `wp_nav_menu([ 'theme_location' => 'mobile-games', ... ])`
  als untere App-Leiste rendern.
- **Sichtbarkeit per CSS** (neue Datei `assets/css/pages/games.css`):

  ```css
  /* Games-Leiste standardmäßig versteckt (Desktop/Tablet) */
  .vpg-games-bar { display: none; }

  /* Nur auf Handys sichtbar */
  @media (max-width: 768px) {
    .vpg-games-bar {
      display: flex;
      position: fixed;
      bottom: 0; left: 0; right: 0;
      z-index: 50;
    }
  }
  ```

- **Pflege:** Inhalte später frei über *Design → Menüs* in WordPress, kein Code nötig.

**Hinweis:** `display:none` versteckt nur optisch — der Markup wird trotzdem
geladen. Bei wenigen Menü-Links irrelevant. Wichtig ist nur, dass das **schwere
Spiel-JS** NICHT global geladen wird (siehe Baustein 5).

---

### Baustein 2 — Spiele-Struktur

Zwei Varianten, eine Empfehlung.

#### Variante A — CPT `vpg_game` *(empfohlen)*
- Registrierung in `inc/cpts.php` analog zu den bestehenden CPTs.
- Felder pro Spiel: Titel, Beschreibung, Cover-Bild, „game key" (z. B. `memory`),
  Konfiguration (z. B. Foto-Set, Schwierigkeit).
- Template `single-vpg_game.php` lädt anhand des „game key" das passende JS.
- **Vorteil:** Neues Spiel = neuer Eintrag im Backend, kein Code-Deploy.
- Passt zur stark CPT-basierten Architektur des Themes.

#### Variante B — Page-Template + Shortcode
- `templates/page-game.php` + Shortcode `[vpg-game id="memory"]`.
- **Vorteil:** schnell für 1–2 Spiele.
- **Nachteil:** weniger skalierbar, jedes neue Spiel braucht Hand-Arbeit.

> **Empfehlung: Variante A.**

---

### Baustein 3 — Das erste Spiel (Touch-First)

- Spiel-Logik als `assets/js/games/<name>.js`
  (Empfehlung Start: **Memory mit VPG-Fotos** — mobil von Haus aus ideal).
- **Touch-First** entwickeln:
  - `touchstart` / `touchend` statt nur Maus/Tastatur,
  - responsives Grid/Canvas, skaliert mit der Bildschirmbreite,
  - keine Pfeiltasten-Steuerung (auf Handy nicht vorhanden).
- Schlank halten → läuft auch auf älteren Geräten flüssig.

**Spiel-Eignung für Mobile:**

| Spiel-Typ | Mobile-Eignung | Aufwand |
|---|---|---|
| Memory (Tippen) | ⭐⭐⭐ nativ | gering |
| Foto-Quiz (Tippen) | ⭐⭐⭐ nativ | gering |
| Reaktionsspiel (Tippen) | ⭐⭐⭐ nativ | gering |
| Snake / 2048 (Wischen) | ⭐⭐ braucht Swipe-Gesten | mittel |
| Maus-/Tastatur-Spiele | ⭐ braucht On-Screen-Controls | hoch |

---

### Baustein 4 — Scoreboard (Kern)

**Speicherung — zwei Optionen:**

| Option | Vorteil | Nachteil |
|---|---|---|
| **A) Eigene Tabelle `wp_vpg_scores`** *(empfohlen)* | saubere, schnelle Ranglisten-Sortierung (`ORDER BY score DESC LIMIT 10`) | minimale DB-Anlage nötig |
| B) `update_user_meta` (wie Bookmarks) | kein Schema, kopiert vorhandenes Muster | für Top-Listen schlecht sortier-/skalierbar |

**Empfohlenes Schema `wp_vpg_scores`:**

```
id          BIGINT  PK AUTO_INCREMENT
user_id     BIGINT  (FK → wp_users)
game        VARCHAR (game key, z. B. "memory")
score       INT
meta        TEXT    (optional: JSON mit Zeit/Level)
created_at  DATETIME
```

**AJAX-Handler** (neue Datei `inc/games.php`), Vorbild `inc/members.php:95`:

```php
add_action( 'wp_ajax_vpg_save_score', function () {
    if ( ! is_user_logged_in() ) wp_send_json_error( 'login required', 403 );
    check_ajax_referer( 'vpg_score' );

    $game  = sanitize_key( $_POST['game'] ?? '' );
    $score = absint( $_POST['score'] ?? 0 );

    // Server-seitige Plausibilität (Anti-Cheat, grob)
    if ( ! $game || $score > VPG_GAME_MAX_SCORE ) {
        wp_send_json_error( 'invalid score' );
    }

    // → in wp_vpg_scores schreiben
    wp_send_json_success( [ 'saved' => true ] );
} );
```

**Anzeige:** Shortcode `[vpg-scoreboard game="memory" top="10"]`
→ rendert eine **responsive Top-Liste** (HTML-Tabelle, mobil unproblematisch).

---

### Baustein 5 — Verdrahtung

1. **Modul laden:** `require_once VPG_V2_DIR . '/inc/games.php';` in `functions.php`
   im Block „Phase-2 Extensions".
2. **Assets bedingt laden** in `inc/enqueue.php`:
   - Games-CSS global *(nur Menü-Leiste, sehr klein)* ODER nur wo nötig,
   - Spiel-JS **ausschließlich** bei `is_singular('vpg_game')`,
   - Nonce + AJAX-URL via `wp_localize_script('vpg-game', 'VPG_GAME', [...])`
     (wie beim Bookmark-Script).
3. **Setup-Wizard** (`inc/setup-wizard.php`) optional erweitern:
   Games-Übersichtsseite + Menü-Zuordnung automatisch anlegen.

---

## 4. Sicherheit & Fairness

- **Nonce-Schutz** bei jedem Score-Speichern (`check_ajax_referer`).
- **Nur eingeloggte Mitglieder** dürfen Scores speichern → nutzt vorhandenes Gating;
  schützt vor anonymem Spam.
- **Server-seitige Plausibilitätsprüfung** (Wertebereich, ggf. Mindest-Spielzeit):
  Reine JS-Spiele sind im Browser manipulierbar. Für ein Community-Spaß-Feature
  reicht Nonce + eingeloggter User + grobe Bereichsprüfung. Für „harte"
  Ranglisten wäre serverseitige Validierung der Spiel-Logik nötig (deutlich
  aufwändiger — bewusst **out of scope** für v1).
- **Eingaben sanitizen** (`sanitize_key`, `absint`) konsequent.

---

## 5. Performance

- Spiel-JS wird **nicht global** geladen, sondern nur auf der Spiel-Seite.
- Bilder (z. B. Memory-Karten) über vorhandene WebP-/Bild-Pipeline ausliefern.
- Spiel-Code schlank halten, keine schweren Game-Engines/Frameworks.
- Menü-Leiste ist reines CSS/HTML → kein messbarer Overhead.

---

## 6. Entscheidungen & Trade-offs (Zusammenfassung)

| Thema | Empfehlung | Begründung |
|---|---|---|
| Spiele-Struktur | CPT `vpg_game` | skalierbar, kein Code pro Spiel |
| Score-Speicher | eigene Tabelle `wp_vpg_scores` | saubere Ranglisten-Sortierung |
| Score speichern | nur eingeloggte Mitglieder | Manipulationsschutz, nutzt Gating |
| Erstes Spiel | Memory mit VPG-Fotos | mobil nativ, thematisch passend |
| Anti-Cheat v1 | Nonce + Bereichsprüfung | pragmatisch; harte Validierung später |

---

## 7. Offene Punkte (vor Umsetzung zu klären)

1. **Welches erste Spiel?** Memory (Fotos) / Foto-Quiz / Reaktionsspiel.
2. **Score nur für Mitglieder** oder auch Gäste (mit Namenseingabe)?
3. **DB-Tabelle** oder **User-Meta** für die Scores?
4. **Menü-Stil:** feste untere App-Leiste oder klassisches mobiles Burger-Menü?

---

## 8. Betroffene / neue Dateien (Überblick)

**Neu:**
- `inc/games.php` — CPT-Registrierung optional + AJAX-Handler + Scoreboard-Shortcode
- `single-vpg_game.php` — Spiel-Template
- `assets/js/games/memory.js` (o. ä.) — Spiel-Logik
- `assets/css/pages/games.css` — Games-Leiste + Spiel-Styling

**Geändert:**
- `functions.php` — `inc/games.php` einhängen
- `inc/theme-setup.php` — Menü-Location `mobile-games`
- `inc/enqueue.php` — bedingtes Laden + Nonce-Übergabe
- `footer.php` — mobile Games-Leiste ausgeben
- `inc/cpts.php` — CPT `vpg_game` *(bei Variante A)*
- `inc/setup-wizard.php` — Auto-Seite *(optional)*

---

## 9. Vorgeschlagene Umsetzungs-Reihenfolge

1. Menü-Location + mobile Leiste + CSS (sichtbar nur auf Handy) — **kleinster Schritt, sofort sichtbar**
2. CPT `vpg_game` + Spiel-Template
3. Erstes Spiel (Memory) touch-first
4. Scoreboard: Tabelle + AJAX-Handler + Shortcode
5. Gating + Anti-Cheat-Bereichsprüfung
6. Setup-Wizard-Integration (optional)

---

*Letzte Aktualisierung: 2026-06-15 — reine Planung, keine Code-Änderungen am Theme.*
