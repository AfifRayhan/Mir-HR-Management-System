# Design: Employee Reset Password Button

## Overview
Add a "Reset Password" button to the employee profile page (`/employee-profile`) that allows employees to change their passwords via a modal interaction.

## Goals
- Provide a convenient way for employees to change their passwords.
- Use an interactive modal to avoid page reloads.
- Ensure security by requiring the current password.

## Components

### 1. UI Button
- **Location**: Top right of the `profile-header` in `resources/views/personnel/employees/profile.blade.php`.
- **Style**: Modern outline button with a shield-lock icon.
- **Trigger**: JavaScript function `resetPasswordModal()`.

### 2. Password Reset Modal (SweetAlert2)
- **Fields**:
  - `current_password` (password type)
  - `password` (new password, password type)
  - `password_confirmation` (password type)
- **Validation**:
  - Required fields.
  - New password must match confirmation.
  - Minimum length (enforced by backend).
- **Submission**: AJAX (Axios) request to the `password.update` route.

### 3. Backend Handling
- **Route**: `password.update` (already exists in `routes/auth.php`).
- **Controller**: `App\Http\Controllers\Auth\PasswordController@update`.
- **Compatibility**: The controller already uses `validateWithBag('updatePassword', ...)`, which works with AJAX if the `Accept: application/json` header is sent. We might need to handle the response specifically for AJAX to show clear error messages.

## Data Flow
1. User clicks "Reset Password".
2. Modal opens.
3. User enters passwords and clicks "Update".
4. Axios sends PUT request to `/password`.
5. Backend validates:
   - If success: Return success response, Modal closes, Show success toast.
   - If failure: Return 422 with errors, Display errors in the modal.

## Success Criteria
- Employees can successfully change their password from the profile page.
- Errors are clearly communicated without refreshing the page.
- Security constraints (current password requirement) are maintained.
