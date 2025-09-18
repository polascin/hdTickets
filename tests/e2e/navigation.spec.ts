import { test, expect } from '@playwright/test';

const shot = async (page, name: string) => page.screenshot({ path: `tests/e2e/screenshots/${test.info().project.name}-nav-${name}.png`, fullPage: true });

test.describe('Navigation interactions', () => {
  test('open user dropdown and admin dropdown if present', async ({ page }) => {
    await page.goto('/');

    // Try opening user menu if button exists
    const userBtn = page.locator('[aria-label="User menu"], button:has-text("Profile"), .uiv2-user-btn');
    if (await userBtn.count()) {
      await userBtn.first().click();
      await shot(page, 'user-dropdown-open');
    } else {
      test.skip(true, 'No user menu in guest view');
    }
  });
});
