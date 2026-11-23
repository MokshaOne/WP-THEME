// @ts-check
// #24 — Visual smoke test. A tiny Playwright check that the hero, the portfolio
// rail and the enquire form render, and that the date-picker icon is visible.
// Run against a live/staging URL:  BASE_URL=https://staging.example npx playwright test
const { test, expect } = require('@playwright/test');

const BASE = process.env.BASE_URL || 'http://localhost:8889';

test('home hero renders', async ({ page }) => {
  await page.goto(BASE + '/');
  await expect(page.locator('.nr-hero, [data-hero]').first()).toBeVisible();
  await expect(page.locator('.nr-hero__title, h1').first()).toBeVisible();
});

test('portfolio rail renders', async ({ page }) => {
  const res = await page.goto(BASE + '/portfolio/');
  // Either the dedicated archive or a generic card rail should appear.
  if (res && res.ok()) {
    await expect(page.locator('.nr-portfolio-rail, .nr-card, .nr-page').first()).toBeVisible();
  }
});

test('enquire form + visible date picker', async ({ page }) => {
  const res = await page.goto(BASE + '/enquire/');
  if (!res || !res.ok()) test.skip(true, 'No enquire page on this install');
  const date = page.locator('input[type="date"]').first();
  if (await date.count()) {
    await expect(date).toBeVisible();
    // The calendar indicator must carry our white SVG background (not the
    // invisible v4.38 glyph). We assert the computed background-image is set.
    const bg = await date.evaluate((el) => {
      const ind = window.getComputedStyle(el, '::-webkit-calendar-picker-indicator');
      return ind ? ind.getPropertyValue('background-image') : '';
    });
    expect(bg).toContain('svg');
  }
});
