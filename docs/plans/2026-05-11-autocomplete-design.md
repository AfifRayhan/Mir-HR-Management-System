# Design Document - Floating Autocomplete Employee Search

## Goal
Replace the current two-step search (Type -> Filter Dropdown -> Select) with a unified, modern autocomplete component that shows suggestions automatically as the user types.

## Proposed Design

### 1. UI Structure
- **Container**: A relative wrapper around the search input.
- **Suggestions List**: A position-absolute `div` that appears below the input.
    - Style: White background, `z-index: 1000`, `max-height: 300px`, `overflow-y: auto`, `box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1)`.
- **Suggestion Item**: A clickable row showing `ID - Name`.

### 2. Logic & Interactions
- **Live Search**: On `input` event, filter the cached employee list.
- **Visibility**: List only shows if the search input has 1+ characters and is focused.
- **Keyboard Navigation**:
    - `ArrowDown`/`ArrowUp`: Move focus through suggestions.
    - `Enter`: Select highlighted suggestion.
    - `Escape`: Close list.
- **Selection Action**:
    - Update the search input value.
    - Set the hidden `user_id` value.
    - Trigger `loadTree(userId)` instantly.

### 3. Benefits
- **Speed**: One-click selection from a filtered list.
- **Context**: Suggestions appear right where the user is typing.
- **Modernity**: Matches the premium, interactive feel of the HRMS dashboard.

## Success Criteria
- Suggestions appear automatically on typing.
- Clicking a suggestion loads the permission tree without further interaction.
- Keyboard navigation works as expected.
