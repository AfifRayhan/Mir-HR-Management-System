# Employee Reset Password Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Add a "Reset Password" button to the employee profile page that opens a SweetAlert2 modal for secure password updates.

**Architecture:** Frontend-driven modal using SweetAlert2 and Axios, communicating with the existing `password.update` backend route.

**Tech Stack:** Laravel, Blade, SweetAlert2, Axios, Bootstrap 5.

---

### Task 1: Add Reset Password Button to Profile Header

**Files:**
- `[ ]` Modify `resources/views/personnel/employees/profile.blade.php`

**Step 1: Locate profile header and add button**
Add a button next to the employee ID badge in the profile header (around line 191).

```html
<button type="button" onclick="openResetPasswordModal()" class="btn btn-outline-primary btn-sm ms-3">
    <i class="bi bi-shield-lock me-2"></i>{{ __('Reset Password') }}
</button>
```

---

### Task 2: Implement JavaScript for Reset Password Modal

**Files:**
- `[ ]` Modify `resources/views/personnel/employees/profile.blade.php` (inside `@push('scripts')`)

**Step 1: Add the `openResetPasswordModal` function**
This function will use SweetAlert2 to show a form and Axios to submit it.

```javascript
@push('scripts')
<script>
async function openResetPasswordModal() {
    const { value: formValues } = await Swal.fire({
        title: 'Reset Password',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" id="swal-current-password" class="form-control" placeholder="Enter current password">
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" id="swal-new-password" class="form-control" placeholder="Min 8 characters">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" id="swal-confirm-password" class="form-control" placeholder="Repeat new password">
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Update Password',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const current_password = document.getElementById('swal-current-password').value;
            const password = document.getElementById('swal-new-password').value;
            const password_confirmation = document.getElementById('swal-confirm-password').value;

            if (!current_password || !password || !password_confirmation) {
                Swal.showValidationMessage('Please fill in all fields');
                return false;
            }

            if (password !== password_confirmation) {
                Swal.showValidationMessage('New passwords do not match');
                return false;
            }

            return axios.put('{{ route('password.update') }}', {
                current_password,
                password,
                password_confirmation
            }, {
                headers: { 'Accept': 'application/json' }
            }).catch(error => {
                if (error.response && error.response.data && error.response.data.errors) {
                    const firstError = Object.values(error.response.data.errors)[0][0];
                    Swal.showValidationMessage(firstError);
                } else {
                    Swal.showValidationMessage('An error occurred. Please try again.');
                }
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    });

    if (formValues) {
        Swal.fire({
            icon: 'success',
            title: 'Password Updated',
            text: 'Your password has been changed successfully.',
            timer: 2000,
            showConfirmButton: false
        });
    }
}
</script>
@endpush
```

---

### Task 3: Verify and Test

**Step 1: Manual Verification**
1. Navigate to `/employee-profile`.
2. Click the "Reset Password" button.
3. Verify the modal appears.
4. Test with incorrect current password (should show error).
5. Test with mismatching new passwords (should show error).
6. Test with valid data (should show success and update password).

**Step 2: Run existing tests to ensure no regressions**
Run: `php.exe artisan test --filter PasswordUpdateTest` (if exists, or run general auth tests)
