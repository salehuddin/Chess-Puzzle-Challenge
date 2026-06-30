# Forgot Password

The forgot password page allows users to request a password reset link.

## Flow

1. User enters their email address.
2. If the email exists in the system, a reset link is sent.
3. The user receives an email with a link to reset their password.
4. The link expires after a set time (configured in the application).

## Related Files

- `app/Http/Controllers/Auth/PasswordResetLinkController.php` — The controller
- `resources/views/auth/forgot-password.blade.php` — The template
- `routes/auth.php` — Route: `/forgot-password`
