# Unified UI Refactor Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Refactor the entire frontend into a unified, modular CSS system with the `ui-` prefix while maintaining clear visual and logical separation between HR, Employee, and Team Lead views.

**Architecture:** Use a scoped design system where shared `ui-` components are themed via top-level role classes (`.ui-scope-admin`, `.ui-scope-emp`, `.ui-scope-lead`).

**Tech Stack:** Laravel (Blade), Bootstrap 5, Vite, Vanilla CSS.

---

### Task 1: Foundation & Variables
**Goal**: Establish the core design tokens and the role-scoping mechanism.

**Files:**
- `[ ]` Create `resources/css/ui-variables.css`
- `[ ]` Create `resources/css/ui-layout.css`
- `[ ]` Update `resources/css/app.css`

**Step 1: Define variables and role scopes**
Create `resources/css/ui-variables.css`.

**Step 2: Commit**
```bash
git add resources/css/ui-variables.css
git commit -m "style: add unified ui variables and role scopes"
```

---

### Task 2: Refactor Main Layouts
**Goal**: Update the layout files to use the new `ui-` prefix and apply role scopes.

**Files:**
- `[ ]` Update `resources/views/layouts/app.blade.php`
- `[ ]` Update `resources/views/hr-dashboard.blade.php`
- `[ ]` Update `resources/views/employee-dashboard.blade.php`

**Step 1: Update app.blade.php to load new CSS**
Update `app.blade.php` to include the unified CSS.

**Step 2: Apply scope to dashboards**
Update `hr-dashboard.blade.php` to use `<div class="ui-layout ui-scope-admin">`.
Update `employee-dashboard.blade.php` to use `<div class="ui-layout ui-scope-emp">`.

---

### Task 3: Bulk Class Replacement
**Goal**: Perform systematic replacement of `hr-` and `emp-` classes with `ui-` classes.

**Step 1: Replace HR prefixes**
Use regex to replace `hr-layout` with `ui-layout`, `hr-panel` with `ui-panel`, etc.

**Step 2: Replace EMP prefixes**
Use regex to replace `emp-layout` with `ui-layout`, `emp-panel` with `ui-panel`, etc.

---

### Task 4: Extract Inline Styles
**Goal**: Move inline `<style>` blocks from 35+ files into `ui-utilities.css`.

**Files:**
- `[ ]` Create `resources/css/ui-utilities.css`
- `[ ]` Remove `<style>` from specific views.

---

### Task 5: Final Asset Cleanup
**Goal**: Remove the old `custom-*.css` files and finalize `app.css`.

**Files:**
- `[ ]` Delete old files.
- `[ ]` Finalize `app.css`.
