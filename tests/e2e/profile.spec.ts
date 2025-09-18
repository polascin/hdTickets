import { test, expect } from '@playwright/test';

const shot = async (page, name: string) => page.screenshot({ path: `tests/e2e/screenshots/${test.info().project.name}-profile-${name}.png`, fullPage: true });

// Requires E2E_EMAIL/E2E_PASSWORD to be set
// Verifies profile page structure and a simple settings interaction if available

test.describe('Profile and settings (auth)', () => {
  test('profile page renders and basic interactions work', async ({ page }) => {
    const email = process.env.E2E_EMAIL;
    const password = process.env.E2E_PASSWORD;
    if (!email || !password) test.skip(true, 'No E2E_EMAIL/PASSWORD provided');

    await page.goto('/login');
    await page.getByLabel(/email/i).fill(email);
    await page.getByLabel(/password/i).fill(password);
    await page.getByRole('button', { name: /log in|sign in/i }).click();

    await page.goto('/profile');
    await expect(page).toHaveURL(/profile/);
    await shot(page, 'profile');

    // Try toggling a theme switch if present
    const themeToggle = page.locator('#theme-toggle, .uiv2-icon-btn:has(svg)');
    if (await themeToggle.count()) {
      await themeToggle.first().click();
      await shot(page, 'theme-toggled');
    }

    // If a settings form exists, try focusing and blurring a field
    const nameField = page.getByLabel(/name/i).first();
    if (await nameField.count()) {
      await nameField.focus();
      await nameField.blur();
      await shot(page, 'settings-focus');
    }
  });
});
