import { test, expect } from '@playwright/test';

const shot = async (page, name: string) => page.screenshot({ path: `tests/e2e/screenshots/${test.info().project.name}-kbd-${name}.png`, fullPage: true });

test.describe('Keyboard navigation basics', () => {
  test('tab to navigation and open dropdown by keyboard (if present)', async ({ page }) => {
    await page.goto('/');
    await page.keyboard.press('Tab'); // focus first interactive element

    const nav = page.locator('#navigation, #main-navigation');
    if (await nav.count()) {
      // Attempt to focus a button in nav
      const focusable = nav.locator('a, button').first();
      if (await focusable.count()) {
        await focusable.focus();
        await shot(page, 'nav-focus');
        // Try opening with Enter/Space
        await page.keyboard.press('Enter');
        await shot(page, 'nav-enter');
      }
    }
  });
});
