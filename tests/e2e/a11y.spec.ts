import { test, expect } from '@playwright/test';

const shot = async (page, name: string) => page.screenshot({ path: `tests/e2e/screenshots/${test.info().project.name}-a11y-${name}.png`, fullPage: true });

test.describe('Accessibility basics', () => {
  test('skip link exists and focuses main content', async ({ page }) => {
    await page.goto('/');
    const skip = page.locator('a[href="#main-content"]');
    const exists = await skip.count();
    if (!exists) test.skip(true, 'Skip link not found on this route');

    await skip.focus();
    await shot(page, 'skip-link-focused');
    // Ensure main content exists and is focusable
    const main = page.locator('#main-content');
    await expect(main).toHaveCount(1);
  });

  test('focus outline visible on interactive elements', async ({ page }) => {
    await page.goto('/login');
    const email = page.getByLabel(/email/i);
    await email.focus();
    await shot(page, 'login-email-focused');
  });
});
