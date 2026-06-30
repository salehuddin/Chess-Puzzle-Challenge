# Login

The login page allows registered users to sign in to their account.

## Fields

- **Email** — The email address used during registration.
- **Password** — The account password.
- **Remember Me** — Checkbox to stay logged in across browser sessions.

## Flow

1. User enters their email and password.
2. If credentials are correct, they're redirected to the dashboard.
3. If credentials are wrong, an error message is shown.
4. If the user has not verified their email, they may be prompted to do so.

## Related Files

- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` — The login controller
- `resources/views/auth/login.blade.php` — The login template
- `routes/auth.php` — Route: `/login`
