# McPrices Orchestrator Workflow

## Goal
Run the full native Kadence enhancement workflow in one go while still following a feature-by-feature approval loop.

## Workflow
1. Read `spec.md` and `agent.md`.
2. Break work into feature units from `spec.md`.
3. Document intended technical approach in `implementor.md`.
4. Implement the current feature natively in the Kadence parent theme.
5. Verify locally.
6. Apply the `reviewer.md` rubric.
7. Update `status.md` with feature score, overall score, and missing items.
8. If the feature is below `100%`, fix the gaps immediately and repeat review.
9. Move to the next feature only when the current feature is genuinely complete.
10. Finish with a final review pass and final `status.md` update.

## Non-Negotiables
- No child theme.
- No static detached HTML integration that bypasses Kadence builder behavior.
- No breaking of WordPress admin customization for header, footer, or editable front-page content.
- No skipping reviewer scoring.

## Required Outputs
- `spec.md`
- `implementor.md`
- `reviewer.md`
- `status.md`
- `orchestrator.md`
- `agent.md`

## Definition of Done
- The enhanced design is implemented as a native Kadence parent-theme part.
- Search and tabs work.
- Header, footer, and body remain admin-editable.
- Reviewer scoring is updated and explicit.
