# HD Tickets Accessibility Testing Guide

This guide helps you test and verify the accessibility enhancements implemented in HD Tickets.

## Quick Setup for Testing

### Enable Debug Mode
Add `?debug=a11y` to any URL to enable accessibility debug mode, which will:
- Highlight focusable elements with red dashed outlines
- Show ARIA attributes with colored backgrounds
- Display role attributes with dotted borders

Example: `http://hdtickets.local/dashboard?debug=a11y`

### Other Debug Modes
- `?debug=headings` - Shows heading hierarchy
- `?debug=tabindex` - Shows tab order numbers

### Keyboard Shortcuts
- `Ctrl+Shift+A` - Toggle accessibility debug mode
- `Alt+H` - Navigate to home/dashboard
- `Alt+M` - Focus main content
- `Alt+N` - Focus navigation menu

## Testing Checklist

### ✅ Keyboard Navigation
- [ ] **Tab Navigation**: Can navigate through all interactive elements using Tab/Shift+Tab
- [ ] **Skip Links**: Press Tab on page load - skip links should appear at top
- [ ] **Focus Indicators**: All focused elements have visible focus indicators
- [ ] **Escape Key**: Closes modals, dropdowns, and clears search inputs
- [ ] **Arrow Keys**: Navigate through menus, tabs, and other grouped elements
- [ ] **Enter/Space**: Activates buttons and custom controls

### ✅ Screen Reader Support
- [ ] **Live Regions**: Screen reader announces status messages and alerts
- [ ] **Form Validation**: Screen reader announces form errors
- [ ] **Navigation**: Screen reader can identify main navigation, content areas
- [ ] **Headings**: Proper heading hierarchy (h1 → h2 → h3, etc.)
- [ ] **Labels**: All form inputs have associated labels
- [ ] **Descriptions**: Complex elements have helpful descriptions

### ✅ Visual Accessibility
- [ ] **Focus Visible**: Keyboard focus is clearly visible on all elements
- [ ] **High Contrast**: Test with Windows High Contrast mode
- [ ] **Reduced Motion**: Test with `prefers-reduced-motion: reduce`
- [ ] **Color Contrast**: Text has sufficient contrast ratios
- [ ] **Touch Targets**: Interactive elements are at least 44px in size

### ✅ Semantic Markup
- [ ] **Landmarks**: Page has proper landmark regions (nav, main, header, footer)
- [ ] **ARIA Roles**: Custom components have appropriate ARIA roles
- [ ] **ARIA States**: Dynamic content updates ARIA states (expanded, selected, etc.)
- [ ] **Error Messages**: Form errors are properly associated with inputs

## Testing Tools

### Browser Developer Tools
1. **Chrome DevTools**:
   - Open DevTools → Lighthouse → Accessibility audit
   - Elements tab → Accessibility pane

2. **Firefox Developer Tools**:
   - Developer Tools → Accessibility Inspector

### Screen Reader Testing
- **Windows**: NVDA (free) or JAWS
- **macOS**: VoiceOver (built-in)
- **Linux**: Orca (free)

### Browser Extensions
- **axe DevTools** (Chrome/Firefox)
- **WAVE Web Accessibility Evaluator**
- **Accessibility Insights for Web**

## Common Tests

### 1. Skip Link Test
1. Load any page
2. Press Tab once
3. Skip link should appear at top of page
4. Press Enter on skip link
5. Focus should move to main content area

### 2. Form Validation Test
1. Find a form with required fields
2. Try to submit without filling required fields
3. Check that:
   - Screen reader announces errors
   - Focus moves to first error field
   - Error messages are visible and properly associated

### 3. Modal Focus Test
1. Open any modal dialog
2. Check that:
   - Focus moves to modal when opened
   - Tab key is trapped within modal
   - Escape key closes modal
   - Focus returns to trigger element when closed

### 4. High Contrast Mode Test
1. **Windows 10/11**:
   - Settings → Ease of Access → High contrast → Turn on high contrast
2. **macOS**:
   - System Preferences → Accessibility → Display → Increase contrast
3. Verify all content is still readable and functional

### 5. Reduced Motion Test
1. **Windows**: Settings → Ease of Access → Display → Show animations
2. **macOS**: System Preferences → Accessibility → Display → Reduce motion
3. **Browser**: DevTools → Rendering → Emulate CSS prefers-reduced-motion
4. Verify animations are reduced or disabled

## Error Scenarios to Test

### Forms
- [ ] Submit form with missing required fields
- [ ] Submit form with invalid email format
- [ ] Submit form with passwords that don't match
- [ ] Test with JavaScript disabled

### Navigation
- [ ] Try to navigate with only keyboard
- [ ] Test with screen reader
- [ ] Test on mobile device with screen reader

### Dynamic Content
- [ ] Loading states announce properly
- [ ] Success/error messages are announced
- [ ] Modal opening/closing is announced
- [ ] Page navigation changes are announced

## Mobile Accessibility Testing

### Touch Targets
- [ ] All interactive elements are at least 44px (iOS) or 48px (Android)
- [ ] Elements have adequate spacing between them
- [ ] No accidental activations occur

### Screen Reader on Mobile
- **iOS VoiceOver**: Settings → Accessibility → VoiceOver
- **Android TalkBack**: Settings → Accessibility → TalkBack

### Gestures
- [ ] Swipe left/right to navigate elements
- [ ] Double-tap to activate elements
- [ ] Two-finger swipe to scroll content

## Automated Testing Integration

### Jest + Testing Library
```javascript
// Example accessibility test
import { render, screen } from '@testing-library/react';
import { axe, toHaveNoViolations } from 'jest-axe';

expect.extend(toHaveNoViolations);

test('dashboard has no accessibility violations', async () => {
  const { container } = render(<Dashboard />);
  const results = await axe(container);
  expect(results).toHaveNoViolations();
});
```

### Cypress Accessibility Testing
```javascript
// cypress/integration/accessibility.spec.js
describe('Accessibility Tests', () => {
  beforeEach(() => {
    cy.visit('/dashboard');
    cy.injectAxe();
  });

  it('Has no detectable a11y violations on load', () => {
    cy.checkA11y();
  });

  it('Navigates with keyboard', () => {
    cy.get('body').tab().tab();
    cy.focused().should('contain', 'Skip to main content');
  });
});
```

## Reporting Issues

When reporting accessibility issues, include:

1. **Browser/Device**: What browser and version
2. **Assistive Technology**: What screen reader or tool
3. **Steps to Reproduce**: Exact steps taken
4. **Expected Behavior**: What should happen
5. **Actual Behavior**: What actually happens
6. **WCAG Guideline**: Which accessibility guideline is violated

## WCAG 2.1 Compliance Levels

### Level A (Minimum)
- [ ] Images have alt text
- [ ] Forms have labels
- [ ] Content is keyboard accessible

### Level AA (Standard)
- [ ] 4.5:1 color contrast for normal text
- [ ] 3:1 color contrast for large text
- [ ] Text can be resized up to 200%
- [ ] Focus indicators are visible

### Level AAA (Enhanced)
- [ ] 7:1 color contrast for normal text
- [ ] 4.5:1 color contrast for large text
- [ ] Context-sensitive help is available

## Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [WebAIM Screen Reader Testing](https://webaim.org/articles/screenreader_testing/)
- [axe Accessibility Rules](https://dequeuniversity.com/rules/axe/)
- [MDN Accessibility](https://developer.mozilla.org/en-US/docs/Web/Accessibility)

## Contact

For accessibility questions or to report issues:
- Create an issue in the project repository
- Tag with `accessibility` label
- Include accessibility testing results
