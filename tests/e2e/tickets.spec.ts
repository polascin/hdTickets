import { test, expect } from '@playwright/test';

const shot = async (page, name: string) => page.screenshot({ path: `tests/e2e/screenshots/${test.info().project.name}-tickets-${name}.png`, fullPage: true });

test.describe('Tickets listing and filters (guest or auth)', () => {
  test('tickets page renders and filter controls exist if available', async ({ page }) => {
    await page.goto('/tickets');
    await shot(page, 'tickets');

    // Try common filter selectors
    const filterBar = page.locator('[data-filter], [data-ticket-filters], form:has(input[name*="price"])');
    if (await filterBar.count()) {
      await shot(page, 'filters-present');
    }
  });
});
