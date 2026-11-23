// @ts-check
// #200 — visual regression snapshots of the key surfaces (manual workflow).
// First run records baselines; later runs diff. BASE_URL=… npx playwright test visual
const { test, expect } = require('@playwright/test');
const BASE = process.env.BASE_URL || 'http://localhost:8889';

for (const [name, path] of [['home', '/'], ['portfolio', '/portfolio/'], ['enquire', '/enquire/']]) {
  test('visual: ' + name, async ({ page }) => {
    const res = await page.goto(BASE + path, { waitUntil: 'networkidle' });
    if (!res || !res.ok()) test.skip(true, 'no ' + name);
    await page.waitForTimeout(800);
    await expect(page).toHaveScreenshot(name + '.png', { fullPage: false, maxDiffPixelRatio: 0.02 });
  });
}
