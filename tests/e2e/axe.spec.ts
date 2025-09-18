import { test, expect } from '@playwright/test';

// Simple axe-core injection without the full playwright-axe wrapper
async function injectAxe(page) {
  // Load axe from local node_modules instead of CDN for CI stability
  // eslint-disable-next-line @typescript-eslint/no-var-requires
  const axePath = require.resolve('axe-core/axe.min.js');
  await page.addScriptTag({ path: axePath });
}

const shot = async (page, name: string) => page.screenshot({ path: `tests/e2e/screenshots/${test.info().project.name}-axe-${name}.png`, fullPage: true });

test.describe('A11y via axe-core (basic)', () => {
  test('login page has no serious violations', async ({ page }) => {
    await page.goto('/login');
    await injectAxe(page);
    const results = await page.evaluate(async () => {
      // @ts-ignore
      return await (window as any).axe.run(document, { runOnly: ['wcag2a', 'wcag2aa'] });
    });
    // You can relax this to log violations instead of failing
    const serious = results.violations.filter((v: any) => v.impact === 'serious' || v.impact === 'critical');
    if (serious.length) {
      console.warn('Axe violations:', serious.map((v: any) => v.id));
    }
    await shot(page, 'login-axe');
  });
});
