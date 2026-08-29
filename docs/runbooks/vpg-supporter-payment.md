# VPG · Supporter-Payment (Stripe) — Runbook

**Stand:** Struktur ist fertig — Tiers (`_vpg_tier`), Feature-Flags
(`vpg_tier_can()`), Gating (`[vpg-members tier="supporter"]`) und die
**manuelle Tier-Zuweisung** im User-Profil (Admin) funktionieren heute.
Payment ist bewusst der letzte Schritt.

## Phase 1 · Ohne Code starten (empfohlen)

1. Stripe-Konto → **Payment Links** anlegen: „Supporter €60/Jahr" und
   „Sustaining €180/Jahr" (recurring yearly, EU-VAT automatisch via Stripe
   Tax).
2. Links auf der Membership-Seite hinter die (dann aktivierten) Buttons.
3. Bei Zahlungseingang (Stripe-Mail) setzt ein Admin das Tier im
   User-Profil → alles andere greift sofort. Bei < 50 Supportern ist das
   völlig okay.

## Phase 2 · Webhook-Automation

Endpoint (im Theme nachrüsten, ~80 Zeilen):
`admin-post.php?action=vpg_stripe_webhook`
- Signatur mit `STRIPE_WEBHOOK_SECRET` prüfen (Konstante in wp-config).
- `checkout.session.completed` → E-Mail matchen → `_vpg_tier` setzen.
- `customer.subscription.deleted` → Tier auf `member` zurück.
- Kein Stripe-SDK nötig: Signaturprüfung ist HMAC-SHA256 über den Payload.

## Rechnungs- & Steuerpflichten (Österreich)

- Stripe Tax für USt aktivieren; Stripe stellt Belege aus (Customer Portal
  verlinken für Selbstverwaltung/Kündigung).
- Kleinunternehmerregelung prüfen (< €35k Umsatz → keine USt, in Stripe als
  „tax behavior: exempt" abbilden) — Steuerberater fragen, nicht raten.
- Terms sind vorbereitet (§6 regelt bereits Jahreslauf + 14-Tage-Widerruf).

## Nie

- Kartendaten berühren (immer Stripe-hosted Checkout).
- Free-Features hinter das Tier schieben — `vpg_tier_features()` erzwingt
  das bereits strukturell; so lassen.
