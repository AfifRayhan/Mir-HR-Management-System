# Design Document - Unique Identifiers in Permission Tree

## Goal
Provide full transparency to HR Admins in the Security Dashboard by displaying the internal "Slug" for every module and link, preventing confusion between identically named items.

## Proposed Design

### 1. UI Enhancements
- **Module Labels**: Next to each top-level module title (e.g., Leave Requests), append the slug in a muted, monospaced format.
- **Link Labels**: Next to each child link name, append the slug.
- **Styling**: Use a small text size and a distinct color (muted gray) to ensure it doesn't distract from the primary labels while remaining readable.

### 2. Logic Clean-up
- Update the `redundantSlugs` array in the partial view to match the current database slugs (`employee-leave-request` and `team-lead-leave-request`). This allows the system to hide genuinely duplicate items if necessary, or at least identify them correctly.

### 3. Benefits
- **No Ambiguity**: Admins can instantly see which "Leave Requests" belongs to the Employee vs Team Lead.
- **Debugging**: Makes it easier to cross-reference seeder changes with the UI.

## Success Criteria
- Every item in the Navigation Access Tree displays its slug.
- The UI remains clean and professional.
- Overlapping items are easily distinguishable.
