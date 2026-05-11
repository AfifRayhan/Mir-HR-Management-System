# Instant Client-Side Employee Search Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Implement a real-time, client-side filtering mechanism for employee selection on the Role Permissions page.

**Architecture:** Fetch all active users on page load to eliminate search-triggered reloads. Use Vanilla JavaScript to filter the dropdown options instantly as the user types in the search box by caching the initial options list.

**Tech Stack:** Laravel (Blade), PHP, JavaScript (Vanilla).

---

### Task 1: Simplify Controller for Full Data Loading
**Files:**
- `[ ]` [MODIFY] [RolePermissionController.php](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/app/Http/Controllers/RolePermissionController.php)

**Step 1: Revert server-side search logic**
Remove the `search` query parameter check in `index()` so it always returns the full list of active users. This is necessary for client-side filtering.

```php
// Replace the current search-aware users query with:
$users = User::with(['role', 'employee'])
    ->where('status', 'active')
    ->orderBy('name')
    ->get();
```

**Step 2: Verify data loading**
Run: `php.exe artisan tinker --execute="echo App\Models\User::where('status', 'active')->count();"`
Expected: A count of all active users, which should match the dropdown count on page load.

**Step 3: Commit**
`git commit -m "refactor: revert server-side search to enable client-side filtering"`

---

### Task 2: Enhance View with Live Filtering UI & JS
**Files:**
- `[ ]` [MODIFY] [index.blade.php](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/resources/views/security/role-permissions/index.blade.php)

**Step 1: Refactor Search Input UI**
Remove the "Find" button and the PHP `request('search')` value binding. Add a "Clear" button that is hidden by default.

**Step 2: Implement Client-Side Filtering Script**
Add logic to the `DOMContentLoaded` listener to:
1. Store all initial `<option>` elements in an array.
2. Listen for `input` events on the search box.
3. Match search terms against the text of the options (Name and ID).
4. Re-render the dropdown with filtered results.

**Step 3: Commit**
`git commit -m "feat: implement instant client-side employee filtering"`

---

### Task 4: Verification
**Step 1: Manual UI Test**
1. Navigate to `/security/role-permissions?manage_by=user`.
2. Type an employee name in the search box.
3. **Requirement**: Dropdown must update instantly on every keystroke.
4. **Requirement**: No page reload should occur.
5. **Requirement**: Selecting an employee from the filtered list must still load the tree via AJAX.
