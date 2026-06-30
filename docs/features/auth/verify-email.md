# Verify Email

The email verification page prompts users to confirm their email address after registration.

## Flow

1. User registers an account.
2. A verification email is sent with a unique link.
3. User clicks the link to verify their email.
4. The email is marked as verified and the user can access the full application.

## If the Link Expires

Users can request a new verification link by clicking "Resend" on the verification page.

## Related Files

- `app/Http/Controllers/Auth/EmailVerificationPromptController.php` — Shows the verification prompt
- `app/Http/Controllers/Auth/VerifyEmailController.php` — Handles the verification link
- `app/Http/Controllers/Auth/EmailVerificationNotificationController.php` — Resends the verification email
- `resources/views/auth/verify-email.blade.php` — The template
- `routes/auth.php` — Route: `/verify-email`
