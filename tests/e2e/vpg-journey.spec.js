// VPG · end-to-end smoke: join → dashboard → submit form.
// Runs against a STAGING/TEST site, never production with real members:
//   BASE_URL=https://staging.viennaphotogroup.com npx playwright test
// The test creates a throwaway member (timestamped email) each run.
const { test, expect } = require('@playwright/test');

const BASE = process.env.BASE_URL || 'http://localhost:8080';
const stamp = Date.now();
const EMAIL = `e2e-${stamp}@example.com`;
const PASS = `E2e-${stamp}-pass!`;

test.describe('VPG member journey', () => {
  test('join is free, instant and lands on the dashboard', async ({ page }) => {
    await page.goto(`${BASE}/join/`);
    await expect(page.locator('form input[name="name"]')).toBeVisible();

    await page.fill('input[name="name"]', 'E2E Tester');
    await page.fill('input[name="email"]', EMAIL);
    await page.fill('input[name="password"]', PASS);

    // The anti-spam time-trap wants a human-paced submit
    await page.waitForTimeout(3500);
    await page.click('form button[type="submit"]');

    await page.waitForURL(/dashboard/);
    await expect(page.locator('body')).toContainText(/Welcome/i);
  });

  test('dashboard shows the onboarding checklist and locked submissions', async ({ page }) => {
    await page.goto(`${BASE}/dashboard/`);
    await expect(page.locator('body')).toContainText(/Getting started|Confirm your email/i);

    // Unverified members must see the verify gate on /submit/
    await page.goto(`${BASE}/submit/`);
    await expect(page.locator('body')).toContainText(/Confirm your .*email|Members only/i);
  });

  test('logged-out visitor sees the free join CTA, never a paywall', async ({ context, page }) => {
    await context.clearCookies();
    await page.goto(`${BASE}/join/`);
    await expect(page.locator('body')).toContainText(/free/i);
    await expect(page.locator('body')).not.toContainText(/€60|wait-list/i);
  });

  test('map archive loads with pins payload and filter toolbar', async ({ page }) => {
    await page.goto(`${BASE}/locations/`);
    await expect(page.locator('#vpg-map')).toHaveAttribute('data-pins', /\[/);
    await expect(page.locator('.vpg-map-filter button[data-type="all"]')).toBeVisible();
  });
});
