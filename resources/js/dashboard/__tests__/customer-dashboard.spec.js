import { describe, expect, it, vi } from 'vitest';
import { customerDashboard } from '../customer-v3';

// Helper to create isolated instance
function createInstance() {
  const inst = customerDashboard();
  // stub fetchWithAuth to control behavior
  inst.fetchWithAuth = vi.fn();
  return inst;
}

describe('customerDashboard module', () => {
  it('formatNumber handles null, object, and numbers gracefully', () => {
    const inst = createInstance();
    expect(inst.formatNumber(null)).toBe('0');
    expect(inst.formatNumber(undefined)).toBe('0');
    expect(inst.formatNumber({})).toBe('0');
    expect(inst.formatNumber(1234)).toBe('1,234');
  });

  it('retry logic increments and stops after maxRetries', async () => {
    const inst = createInstance();
    inst.maxRetries = 2;
    inst.fetchWithAuth.mockRejectedValue(new Error('network'));

    // Spy on refreshData internal recursion via setTimeout by forcing sync calls
    const originalSetTimeout = global.setTimeout;
    vi.spyOn(global, 'setTimeout').mockImplementation((fn) => { fn(); return 0; });

    await inst.refreshData(); // 1st failure -> retry scheduled
    expect(inst.retryCount).toBe(1);
    await inst.refreshData(); // 2nd failure -> retry scheduled
    expect(inst.retryCount).toBe(2);
    await inst.refreshData(); // Beyond max -> should cap
    expect(inst.retryCount).toBe(3); // Final increment
    expect(inst.errorMessage).toMatch(/Failed to update/);

    global.setTimeout = originalSetTimeout;
  });
});
