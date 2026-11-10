# VPG · E2E-Smoke-Tests (Playwright)

Deckt den kritischen Pfad ab: **Join (free, Auto-Login) → Dashboard
(Onboarding, Verify-Gate) → Submit-Sperre unverifiziert → Map lädt**.

## Lokal / gegen Staging ausführen

```bash
npm init -y && npm i -D @playwright/test
npx playwright install chromium
BASE_URL=https://staging.viennaphotogroup.com npx playwright test tests/e2e
```

**Nie gegen Produktion mit echten Members** — jeder Lauf legt einen
Wegwerf-Account (`e2e-<timestamp>@example.com`) an. Auf Staging danach
aufräumen: `wp user delete $(wp user list --field=ID --search='e2e-*') --yes`.

## CI

`.github/workflows/vpg-e2e.yml` läuft per Hand (workflow_dispatch) mit der
Ziel-URL als Eingabe — gedacht für den Klick-Check nach jedem
Staging-Deploy.
