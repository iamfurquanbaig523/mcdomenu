# McPrices Native Kadence Enhancement Spec

## Objective
Upgrade the existing McPrices integration inside the Kadence parent theme so the live site matches `C:\Users\muham\Downloads\mcdonalds-menu-enhanced.html` as closely as possible while remaining a native WordPress/Kadence implementation.

## Hard Rules
- Do not violate any previous rules.
- Design must be as attached.
- We are implementing this as a native part of the Kadence parent theme.
- Never create or use a child theme.
- Header, footer, and body must stay customizable from WordPress admin just like the original theme.
- Kadence builder drag-and-drop behavior must keep working.
- Plugin compatibility must be preserved as much as possible.
- Future price/content updates must be possible from WordPress admin without deployment and without writing code.

## Delivery Model
- The work follows an iterative agile loop.
- A feature only counts as complete when reviewer scoring marks it `100%`.
- If a feature is below `100%`, missing items are recorded in `status.md` and fixed before advancing.
- The full loop still runs in one go for this delivery.

## Feature Scope

### F01. Workflow and Governance Docs
- Create `spec.md`, `implementor.md`, `status.md`, `reviewer.md`, `orchestrator.md`, and `agent.md`.
- Define feature-by-feature execution, reviewer scoring, overall scoring, and hard-rule enforcement.

### F02. Enhanced Data Parity
- Bring in all data and visible content from `mcdonalds-menu-enhanced.html`.
- Do not omit sections, tables, cards, labels, counts, FAQ content, guides, or informational blocks unless a native Kadence integration requires the content to live in header/footer builders instead of page body.

### F03. Native Kadence Header Enhancement
- Update the native Kadence header/menu defaults and seeded menu structure to match the enhanced design intent.
- Header menu must include:
  - `Home`
  - `What's New`
  - `Menu`
  - `Sharers`
  - `Deals`
  - `Guides`
  - `Blogs`
- Remove unwanted link underlines.
- Fix the logo/site title contrast when the header turns white on scroll.
- Preserve Kadence header builder editability.

### F04. Search Functionality
- Fix the broken hero search.
- Search must work on the homepage without leaving the design broken.
- Search should locate matching content from the enhanced menu data and move the visitor to the first useful result.
- Matching content should be visibly highlighted.

### F05. Popular Badge and Layering
- Fix the hidden/overlapped `Popular` badge issue in the hero/right card and any similar stacking problems.

### F06. Category Cards and Hover States
- Match enhanced category card layout and hover behavior.
- Arrow circles must change color correctly on hover.
- Underlines that should not appear must be removed.

### F07. Full Menu Tabs
- Implement fully working full-menu tabs based on the enhanced design.
- Tabs must filter or focus the correct menu sections instead of being visual only.

### F08. Enhanced Homepage Body
- Replace the older limited homepage body with the enhanced homepage body.
- Keep the front page editable through WordPress content editing.
- Preserve WordPress-native behavior for anchors and internal navigation.

### F09. Footer/Data Widgets
- Update footer widget content and footer link groups to match the enhanced design where footer content is managed natively through Kadence footer areas.
- Preserve footer builder editability.

### F10. Review and Status Tracking
- Score each feature in percentage form.
- Maintain an overall completion percentage.
- Record missing items and rework notes in `status.md`.

### F11. WordPress Update Resilience
- Keep critical Kadence and McPrices layout styles available across WordPress core updates and cache rebuilds.
- The safeguard must load independently of the active theme without introducing a child theme or changing admin-managed content.
- Critical styles must use stable asset URLs instead of disposable generated CSS bundles.
- Site-specific Kadence integration code and media must live outside the replaceable parent-theme directory while continuing to use Kadence builders, hooks, settings, and markup.
- An official Kadence update must be able to replace the entire parent-theme directory without removing the McPrices design, navigation behavior, content integration, SEO hooks, or media.
- Release builds must lint and include the update-safety layer.

## Acceptance Criteria
- Live front-end visually matches the enhanced reference closely across hero, categories, menu tabs, deals, informational sections, guides, and footer.
- Header/footer/body remain native Kadence/WordPress-managed pieces.
- No child theme is introduced.
- Search works.
- Tabs work.
- Header navigation is corrected.
- Hover and scroll states are corrected.
- Reviewer scoring is documented in `status.md`.
