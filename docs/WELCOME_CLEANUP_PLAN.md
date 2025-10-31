# Welcome Page Post-Deployment Cleanup Plan

## Overview

This document outlines the cleanup tasks to be performed after the canonical welcome page (`feat/welcome-modern-canonical`) is successfully deployed to production.

## Immediate Post-Deployment (Within 1 week)

### 1. Verify Production Deployment ✅

**Priority:** Critical  
**Owner:** DevOps/Developer

**Tasks:**
- [ ] Verify `/` renders the new canonical welcome page
- [ ] Confirm 308 redirects work: `/welcome`, `/home`, `/welcome-modern`, `/welcome-enhanced`
- [ ] Run Lighthouse audit (target: Performance ≥90, Accessibility 100, SEO 100)
- [ ] Check browser console for errors
- [ ] Test keyboard navigation
- [ ] Verify reduced motion preference works

**Success Criteria:**
- All routes behave as expected
- Lighthouse scores meet or exceed targets
- No console errors
- Accessibility features working

### 2. Remove Legacy Welcome Files 🧹

**Priority:** High  
**Owner:** Developer  
**Estimated Time:** 30 minutes

**Files to Remove:**
```bash
# After confirming production is stable (7+ days)
rm resources/views/welcome-modern.blade.php      # 19K - now redundant
rm resources/views/welcome-enhanced.blade.php    # 35K - legacy variant
rm resources/views/welcome.blade.php.backup      # Backup file
```

**Before Removal:**
1. Ensure canonical welcome page has been stable in production for at least 7 days
2. Verify no references to these files exist:
   ```bash
   grep -r "welcome-modern" app/ routes/ resources/views/
   grep -r "welcome-enhanced" app/ routes/ resources/views/
   ```
3. Create git commit: `chore: remove legacy welcome page variants`

**Rollback Plan:**
- Files are in git history and can be restored if needed
- Backup files exist locally before deletion

### 3. Update WelcomeController Methods 🔧

**Priority:** Medium  
**Owner:** Developer  
**Estimated Time:** 1 hour

After legacy files are removed, clean up `WelcomeController.php`:

```php
// Remove or deprecate these methods:
- public function modernWelcome()
- public function enhancedWelcome()
- public function newWelcome()
- protected function getFallbackModernData()
- protected function getFallbackEnhancedData()
```

Keep only:
- `public function index()` (canonical welcome)
- `public function stats()` (API endpoint)
- Helper methods used by `index()`

**Testing:**
```bash
vendor/bin/pest tests/Feature/WelcomePageCanonicalTest.php
```

## Short-Term Maintenance (Within 1 month)

### 4. Monitor Analytics 📊

**Priority:** Medium  
**Owner:** Product/Marketing

**Metrics to Track:**
- Page load times (target: LCP ≤ 2.5s)
- Bounce rate changes
- Conversion rate (registration/sign-ups)
- User feedback via support channels
- Accessibility complaints or reports

**Dashboard:** Set up monitoring for welcome page metrics

### 5. Gather User Feedback 💬

**Priority:** Medium  
**Owner:** Product Manager

**Methods:**
- User surveys (optional)
- Support ticket analysis
- Analytics heat maps
- Session recordings (with privacy compliance)

**Questions:**
- Is the page loading fast enough?
- Is the content clear and compelling?
- Are there any accessibility barriers?

## Mid-Term Improvements (Within 3 months)

### 6. Apply Patterns to Error Pages 🎨

**Priority:** Low  
**Owner:** Developer  
**Estimated Time:** 3-4 hours

Apply the same accessibility and performance patterns to:
- `resources/views/errors/404.blade.php`
- `resources/views/errors/500.blade.php`
- `resources/views/errors/503.blade.php`

**Checklist per page:**
- [ ] Semantic HTML with landmarks
- [ ] WCAG 2.2 AA compliance
- [ ] British English copy
- [ ] Proper SEO metadata
- [ ] Performance optimisations
- [ ] Reduced motion support

**Create Issue:**
```markdown
Title: [A11Y] Apply welcome page patterns to error pages
Labels: accessibility, enhancement
Reference: docs/WELCOME_CLEANUP_PLAN.md
```

### 7. Content Review and Refresh 📝

**Priority:** Low  
**Owner:** Content/Marketing Team

**Review Areas:**
- Statistics (platforms, users, savings) - ensure up-to-date
- Feature descriptions - align with current capabilities
- Testimonials - add real user quotes if available
- Platform integrations list - verify current integrations

**Frequency:** Quarterly review recommended

### 8. Accessibility Audit Revisit ♿

**Priority:** Medium  
**Owner:** QA/Developer

**Schedule:** 3 months after deployment

**Tasks:**
- [ ] Full manual accessibility audit with screen readers
- [ ] Test with real users with disabilities (if possible)
- [ ] Review WCAG 2.2 compliance as content evolves
- [ ] Update ARIA labels based on actual usage patterns
- [ ] Re-run automated tools (WAVE, axe, Lighthouse)

**Create Issue:**
```markdown
Title: [A11Y] Quarterly accessibility audit - Welcome page
Labels: accessibility, maintenance
Reference: .github/ISSUE_TEMPLATE/accessibility-improvement.md
```

## Long-Term Maintenance (Ongoing)

### 9. Performance Monitoring 📈

**Priority:** Low  
**Owner:** DevOps/Developer

**Tools:**
- Lighthouse CI in GitHub Actions
- Real User Monitoring (RUM) if available
- Core Web Vitals tracking

**Alerts:**
- LCP > 3.5s
- CLS > 0.2
- FID > 200ms

**Action:** Investigate and optimise if metrics degrade

### 10. Content Updates 🔄

**Priority:** As needed  
**Owner:** Marketing/Content Team

**When to Update:**
- New platform integrations added
- Statistics milestones reached
- Feature launches
- Seasonal campaigns
- Rebranding initiatives

**Process:**
1. Update content in `resources/views/welcome.blade.php`
2. Run tests: `vendor/bin/pest tests/Feature/WelcomePageCanonicalTest.php`
3. Check accessibility impact
4. Deploy via normal pipeline

## Cleanup Checklist Summary

- [ ] **Week 1:** Verify production deployment
- [ ] **Week 1:** Monitor initial metrics
- [ ] **Week 2:** Remove backup file if stable
- [ ] **Month 1:** Remove legacy welcome files
- [ ] **Month 1:** Clean up WelcomeController methods
- [ ] **Month 1:** Gather initial user feedback
- [ ] **Month 3:** Apply patterns to error pages
- [ ] **Month 3:** Quarterly accessibility audit
- [ ] **Quarterly:** Content review and refresh
- [ ] **Ongoing:** Performance monitoring

## Notes

### Do Not Remove Until Confirmed Stable
The following should only be removed after 7+ days of stable production:
- `welcome-modern.blade.php`
- `welcome-enhanced.blade.php`

### Maintain Git History
All removed files remain in git history and can be restored if needed:
```bash
# To restore a removed file
git checkout <commit-before-removal> -- path/to/file
```

### Testing Before Each Cleanup
Always run the full test suite before committing cleanup changes:
```bash
vendor/bin/pest
vendor/bin/pint
vendor/bin/phpstan analyse
npm run lint
npm run type-check
```

## Questions or Issues?

For questions about this cleanup plan:
- Technical: See `docs/WELCOME_REBUILD_COMPLETE.md`
- Deployment: See `docs/DEPLOYMENT_VERIFICATION.md`
- GitHub: Use issue template `.github/ISSUE_TEMPLATE/accessibility-improvement.md`

---

**Created:** 2025-10-31  
**Status:** Pending production deployment  
**Next Review:** After production deployment + 7 days
