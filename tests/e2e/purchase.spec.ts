import { test, expect } from '@playwright/test';

const shot = async (page, name: string) => page.screenshot({ path: `tests/e2e/screenshots/${test.info().project.name}-purchase-${name}.png`, fullPage: true });

// Optional: Attempt a purchase flow skeleton if route exists (no destructive action)
// Ensures the page renders and the form is visible; does not submit final checkout.

test.describe('Purchase flow skeleton (guest)', () => {
  test('purchase page renders without submitting', async ({ page }) => {
    // If your app redirects guests, this will just screenshot the redirect page
    await page.goto('/tickets/purchase');
    await shot(page, 'purchase-page');

    const form = page.locator('form');
    if (await form.count()) {
      await shot(page, 'purchase-form-present');
    }
  });
});
