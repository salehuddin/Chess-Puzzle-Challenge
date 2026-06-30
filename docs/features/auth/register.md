# Register

The registration page allows new users to create an account.

## Fields

- **Name** — Display name.
- **Email** — Email address. Must be unique.
- **Password** — Must meet the application's password requirements.
- **Password Confirmation** — Must match the password.

## Flow

1. User fills in the registration form.
2. An account is created and the user is logged in.
3. A verification email is sent (if email verification is enabled).
4. The user is redirected to the dashboard.

## Redirect Behavior

If the user was redirected to registration from a specific page (e.g. a challenge enrollment page), they're sent back to that page after registering. The `redirect_to` query parameter controls this.

## Related Files

- `app/Http/Controllers/Auth/RegisteredUserController.php` — The registration controller
- `resources/views/auth/register.blade.php` — The registration template
- `routes/auth.php` — Route: `/register`
