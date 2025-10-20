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
\n+### Phase 2C (2025-09-23) – Advanced Feature Migration & Prune
Actions:
- Migrated validation messaging, progress indicator, multi-step steps, and section wrapper into semantic layer with `.hdt-form-*` prefixed classes + legacy aliases.
- Removed from `migrated/enhanced-forms.css`: button suite (rely on global `.btn*`), input adornment variants (phone/email icon backgrounds, currency pseudo), multi-step, progress, section, message, and visual validation background fills.
- Kept only minimal structural legacy grid/group patterns pending later grid audit.

Bundle Size (post Phase 2C initial prune):
- app.css: 95.88 kB (−1.94 kB from pre-Phase2C 97.82 kB, still +4.46 kB over Phase 1 baseline)
- welcome.css: 99.67 kB (−1.95 kB from 101.62 kB, +4.46 kB over baseline)

Analysis:
- Net reduction achieved versus pre-prune state, but gap vs. 91.42 kB baseline persists due to remaining structural legacy blocks (grids, responsive overrides, accessibility duplication) and unpruned token overlap.
- Semantic migration now centralizes advanced form UX; further savings will come from: (1) auditing unused legacy modifiers (`form-grid--*`, horizontal/inline variants), (2) deduplicating responsive adjustments now replicated in global layout CSS, and (3) merging overlapping token variables with global design tokens.

Planned Phase 2D (Target to reach or beat baseline):
1. Usage audit of `form-grid`, `form-group--horizontal`, `form-group--inline` to convert to semantic or general layout utilities; prune if low-use.
2. Consolidate media queries (mobile touch target overrides) into semantic layer or global responsive file and remove duplicates.
3. Token merge: map `--form-*` and `--hdt-form-*` to unified naming; eliminate redundancies.
4. Second prune pass to remove any now-orphaned selectors (expected savings 2–4 kB).

Risk & Rollback:
- Aliases retain backward compatibility; removed adornment backgrounds can be reintroduced via inline SVG wrappers if required (low complexity).
- Example view referencing `.form-button` will need update to `.btn` variants if demonstration is kept—pending follow-up.

Next Milestone Success Criteria:
- app.css ≤ 91 kB, welcome.css proportional reduction.
- No missing visual affordances in validation, floating label, progress, multi-step examples.

Status: Phase 2C complete; proceeding to Phase 2D optimization.
\n+### Phase 2D (2025-09-23) – Layout & Token Prune
Actions:
- Introduced semantic grid/layout classes (`.hdt-form-grid`, column variants, horizontal/inline group modifiers) with legacy aliases.
- Removed legacy grid definitions, responsive media queries, and standalone `:root` `--form-*` token block from `migrated/enhanced-forms.css` (now centralized via existing `--hdt-form-*` tokens).
- Eliminated unused horizontal/inline variants not referenced in views (only example view used grid; future example update pending to prefer semantic classes explicitly).

Bundle Size After Phase 2D:
- app.css: 94.94 kB (−0.94 kB from Phase 2C 95.88 kB; −10.20 kB from pre-Phase1 105.14 kB; +3.52 kB over 91.42 kB baseline)
- welcome.css: 98.73 kB (−0.94 kB from 99.67 kB; +3.52 kB over baseline)

Progress Toward Baseline Goal:
- Cumulative reduction since pre-refactor remains strong, but target to re-match/beat 91.42 kB unmet. Residual overhead likely in:
	1. Dual legacy alias selectors inflating rule counts (could transition templates to `.hdt-*` then drop `.form-*`).
	2. Overlapping color/token definitions still present across other migrated CSS bundles (navigation, components) referencing both design-system and local tokens.
	3. `tw-legacy.css` untouched utilities representing latent pruning opportunity once semantic coverage broadens.

Next Optimization Candidates (Phase 2E Proposal):
1. Template sweep replacing `.form-*` usages with `.hdt-*` (scriptable) then remove alias groups (est. saving 2–3 kB).
2. Token unification pass: map `--hdt-form-*` to existing global tokens and remove redundant declarations (0.5–1 kB).
3. Begin selective removal of unused utility groups from `tw-legacy.css` (inventory + prune waves) (variable savings 1–4 kB). 
4. Update example form view to use semantic classes and `.btn` for buttons; delete obsolete example-only classes if unreferenced.

Exit Criteria for Phase 2E:
- app.css ≤ 91 kB.
- No legacy `.form-*` selectors retained except where still referenced in production views (verified via grep).

Status: Phase 2D complete; ready to initiate Phase 2E alias removal & utility pruning strategy.