# CSS Refactor Progress

## Phase 1 – Import Consolidation (2025-09-23)
Objective: Reduce CSS payload and duplication after Tailwind removal by consolidating legacy migrated layers with minimal risk.

Changes Implemented:
- Merged `navigation-enhanced.css` & `navigation-dashboard-fixes.css` into `migrated/navigation.css`.
- Removed `loading-states.css` import; added a lean skeleton loader subset + `.skeleton` alias directly into `app.css`.
- Removed duplicate token import inside `components-v2.css`.
- Preserved higher-risk bundles for dedicated later passes.

Bundle Size Impact (Primary):
- app.css: 105.14 kB → 91.42 kB (−13.72 kB ≈13.0%)
- welcome.css: 108.93 kB → 95.21 kB (−13.72 kB ≈12.6%)

## Phase 2 – Forms Consolidation (In Progress)
Introduced semantic forms layer (`semantic/forms.css`) with canonical `.hdt-*` classes plus legacy aliases (`.form-input`, `.form-group`, etc.) to avoid breaking existing templates. Removed duplicate base form styles from `app.css` and trimmed corresponding block from `enhanced-forms.css`. Initial build after extraction shows intermediate size (includes both semantic + remaining advanced form rules).

Intermediate Post-Phase2 (Step A) Sizes:
- app.css: 96.69 kB (net +5.27 kB from Phase 1 baseline due to temporary coexistence of semantic + remaining legacy advanced rules)
- welcome.css: 100.49 kB (+5.28 kB)

Phase 2B Adjustments:
- Added alias grouping (removed non-standard `composes`) to reduce duplication.
- Began pruning advanced adornment and button patterns from `enhanced-forms.css` (goal: shift unique features into semantic layer or rely on global button styles).
- Post partial prune build (second run reflected cache differences):
	- app.css: 97.82 kB
	- welcome.css: 101.62 kB

Note: Slight growth indicates remaining legacy feature blocks still resident in `enhanced-forms.css`; full migration of validation messages, progress, steps, sections, and responsive overrides not yet optimized. Further consolidation will target net reduction below 91.42 kB baseline in Phase 2C.

Next Steps (Phase 2B):
1. Migrate advanced features (floating label, states, size variants) fully into semantic file (if any remain).
2. Remove now-superfluous sections from `enhanced-forms.css` (target net reduction below Phase 1 baseline).
3. Introduce optional form feedback classes (success/error/warning messages) and retire duplicates.
4. Re-run build and record final Phase 2 size deltas.

Planned Future Phases:
- Theme token unification (`theme-system.css` + `design-system-v2.css`).
- Mobile & grid usage audit for pruning.
- `tw-legacy.css` utility pruning post semantic adoption.

Verification:
- Production builds succeed.
- Legacy class names continue functioning via alias layer.

Baseline References:
- Pre Phase 1: app.css 105.14 kB
- Post Phase 1 Baseline: app.css 91.42 kB

This document will be updated after completing Phase 2B with final consolidated metrics.