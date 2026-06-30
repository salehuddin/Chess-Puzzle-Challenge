# Confirm Password

The confirm password page requires users to re-enter their password before accessing sensitive areas of the application.

## When It Appears

This page is shown when a user tries to access a protected section that requires recent password confirmation. It's a security measure to ensure the authenticated user is still the one making the request.

## Flow

1. User navigates to a sensitive area.
2. They're prompted to enter their password.
3. After confirming, they're granted access to the requested page.

## Related Files

- `app/Http/Controllers/Auth/ConfirmablePasswordController.php` — The controller
- `resources/views/auth/confirm-password.blade.php` — The template
- `routes/auth.php` — Route: `/confirm-password`
