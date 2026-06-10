// @ts-check
// #24 — Playwright config for the Obscura smoke test.
// These run against a live/staging URL (the theme needs a WordPress runtime),
// so they are not part of the per-PR composer check — invoke manually or via
// the workflow_dispatch "Quality" GitHub Action.
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  timeout: 30000,
  retries: 1,
  reporter: [['list']],
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8889',
    headless: true,
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'mobile',   use: { ...devices['Pixel 5'] } },
  ],
});
