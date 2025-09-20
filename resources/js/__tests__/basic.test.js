import { describe, it, expect } from 'vitest';

describe('Basic HD Tickets Tests', () => {
  it('should pass a basic test', () => {
    expect(1 + 1).toBe(2);
  });

  it('should handle string operations', () => {
    expect('HD Tickets').toContain('Tickets');
  });

  it('should handle array operations', () => {
    const sports = ['Football', 'Basketball', 'Baseball'];
    expect(sports).toHaveLength(3);
    expect(sports).toContain('Basketball');
  });
});

describe('Sports Events Utility Tests', () => {
  it('should format event names correctly', () => {
    const formatEventName = (home, away) => `${home} vs ${away}`;
    expect(formatEventName('Lakers', 'Warriors')).toBe('Lakers vs Warriors');
  });

  it('should calculate price ranges', () => {
    const tickets = [
      { price: 50 },
      { price: 100 },
      { price: 25 },
      { price: 150 }
    ];
    
    const minPrice = Math.min(...tickets.map(t => t.price));
    const maxPrice = Math.max(...tickets.map(t => t.price));
    
    expect(minPrice).toBe(25);
    expect(maxPrice).toBe(150);
  });

  it('should validate ticket quantities', () => {
    const validateQuantity = (quantity) => {
      return quantity > 0 && quantity <= 10 && Number.isInteger(quantity);
    };

    expect(validateQuantity(1)).toBe(true);
    expect(validateQuantity(5)).toBe(true);
    expect(validateQuantity(0)).toBe(false);
    expect(validateQuantity(11)).toBe(false);
    expect(validateQuantity(2.5)).toBe(false);
  });
});