# Permission Tree Unique Identifiers Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Update the Role Permissions tree to display slugs for clarity.

**Architecture:** Modify the partial tree view to append the slug to module and link names. Update redundant slug logic to match seeder.

**Tech Stack:** Laravel (Blade), CSS.

---

### Task 1: Update Tree Partial View
**Files:**
- `[ ]` [MODIFY] [tree.blade.php](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/resources/views/security/role-permissions/partials/tree.blade.php)

**Step 1: Fix redundant slugs list**
Update the array to match the actual slugs in the seeder.

**Step 2: Append slug to Module titles**
Update the rendering logic for `$item->name`.

**Step 3: Append slug to Child link names**
Update the rendering logic for `$child->name`.

**Step 4: Commit**
`git commit -m "ui: display slugs in permission tree for better clarity"`

---

### Task 2: Verification
**Step 1: Manual Check**
1. Navigate to `/security/role-permissions`.
2. Verify every item now has a `[slug]` next to it.
3. Verify "Leave Requests" items are distinguishable.
