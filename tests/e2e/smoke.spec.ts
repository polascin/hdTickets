import { test, expect } from '@playwright/test';

const withScreenshot = async (page, name: string) => {
  await page.screenshot({ path: `tests/e2e/screenshots/${test.info().project.name}-${name}.png`, fullPage: true });
};

test.describe('Public pages', () => {
  test('login and register render', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveTitle(/Login|Sign in|HD Tickets/i);
    await withScreenshot(page, 'login');

    await page.goto('/register');
    await expect(page).toHaveTitle(/Register|Sign up|HD Tickets/i);
    await withScreenshot(page, 'register');
  });
});

test.describe('Dashboard smoke (optional auth)', () => {
  test('dashboard loads when authenticated', async ({ page }) => {
    const email = process.env.E2E_EMAIL;
    const password = process.env.E2E_PASSWORD;
    if (!email || !password) test.skip(true, 'No E2E_EMAIL/PASSWORD provided');

    await page.goto('/login');
    await page.getByLabel(/email/i).fill(email);
    await page.getByLabel(/password/i).fill(password);
    await page.getByRole('button', { name: /log in|sign in/i }).click();

    await page.waitForURL(/dashboard|home|\//i, { timeout: 15000 });
    await withScreenshot(page, 'dashboard');

    // Navigate to tickets
    await page.goto('/tickets');
    await withScreenshot(page, 'tickets');
  });
});
