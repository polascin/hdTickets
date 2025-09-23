# TailwindCSS Removal Notes

Date: (auto) Migration phase complete.

## Summary
TailwindCSS has been fully removed from the build pipeline and CDN usage. A frozen snapshot of high-usage utilities and a minimal preflight equivalent now live in `resources/css/tw-legacy.css`. This preserves visual fidelity while we progressively replace utility classes with semantic, design-system oriented styles.

## Key Changes
- Deleted `tailwind.config.js` and removed Tailwind + plugins from `package.json` devDependencies.
- Stripped `@import "tailwindcss/preflight"` and `@import "tailwindcss/utilities"` from `app.css` and `app-v3.css`.
- Added `tw-legacy.css` (frozen utilities) and `base-reset.css` (lightweight reset).
- Removed all `<script src="https://cdn.tailwindcss.com">` references from Blade & test HTML.
- Updated CSP to drop `https://cdn.tailwindcss.com`.
- Updated docs to reflect new styling architecture.

## Transitional Files
| File | Purpose |
|------|---------|
| `resources/css/tw-legacy.css` | Frozen subset of utilities required by existing markup. Do not expand unless regression found. |
| `resources/css/base-reset.css` | Minimal modern reset replacing Tailwind preflight. |

## Replacement Strategy
1. New UI work must use design system classes / components (prefixed with `.hdt-` or semantic component names) instead of Tailwind utilities.
2. When touching a Blade view that still contains Tailwind-like utility classes, refactor only the scope you’re modifying to design-system equivalents; avoid large churn PRs.
3. Track removed utility class groups in a running checklist (see below). Once a group is fully replaced, remove its rule from `tw-legacy.css`.

### Utility Decommission Checklist
- [ ] Spacing (.m-*, .p-*, .gap-*) – migrate to spacing tokens / utility abstractions already in design system.
- [ ] Flex / Grid helpers (.flex, .grid, .items-*, .justify-*) – replace with layout component wrappers or semantic container classes.
- [ ] Typography (.text-sm/base/lg, font-* classes) – rely on tokenized component classes.
- [ ] Color (.bg-*, .text-*) – component variants + token variables.
- [ ] Borders & Radius (.rounded-*, .border, .border-b) – component-level styles.
- [ ] Shadows (.shadow-*) – map to hdt shadow tokens.
- [ ] Positioning / Sizing (e.g. .w-full, .min-h-screen) – integrate into layout shells.

## Removal Milestones
| Milestone | Criteria | Action |
|-----------|----------|--------|
| M1 | No new Tailwind-like classes added | Enforce via code review.
| M2 | 50% of legacy utility groups removed | Prune unused blocks in `tw-legacy.css`.
| M3 | 90% removal & no wildcard selectors rely on them | Delete `tw-legacy.css`, fold any rare survivors into components.

## Risks & Mitigations
| Risk | Mitigation |
|------|-----------|
| Hidden dependency on a removed utility | Keep `tw-legacy.css` minimal but extensible for emergency additions (comment required). |
| Performance regression due to larger frozen file | Periodic pruning as milestones reached. |
| Inconsistent styling during refactor | Prefer component abstractions; avoid one-off inline styles. |

## Next Steps
1. Audit top 10 Blade templates by traffic; refactor utilities to design system classes.
2. Add a style lint rule (future) to flag patterns from `tw-legacy.css`.
3. Document mapping examples (e.g., `.flex.items-center.gap-4` -> `.hdt-flex-row-center-md`).

## Contact
Questions: See `DOCUMENTATION.md` design system section or open an issue under “frontend-modernization”.
