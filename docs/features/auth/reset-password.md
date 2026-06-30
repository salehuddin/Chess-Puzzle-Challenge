# Reset Password

The reset password page allows users to set a new password after clicking the reset link from their email.

## Fields

- **Email** — Pre-filled from the reset link.
- **Password** — The new password.
- **Password Confirmation** — Must match the new password.

## Flow

1. User clicks the reset link in their email.
2. They're taken to this page with their email pre-filled.
3. User enters and confirms their new password.
4. The password is updated and the user is redirected to the login page.

## Related Files

- `app/Http/Controllers/Auth/NewPasswordController.php` — The controller
- `resources/views/auth/reset-password.blade.php` — The template
- `routes/auth.php` — Route: `/reset-password/{token}`
