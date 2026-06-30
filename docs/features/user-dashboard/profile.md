# Profile

The profile page lets players manage their account settings and personal information.

## What Can Be Updated

- **Name** — Display name shown on the Hall of Fame and dashboard.
- **Email** — Email address for login and notifications. Changing it requires re-verification.
- **Password** — Update the account password. Must provide the current password first.
- **Shipping Address** — Address used for medal fulfillment. Fields: address line 1 & 2, city, state, postcode, country.

## How Address Is Used

When a player completes a challenge and submits a medal request, the shipping address from their profile is pre-filled. The address is snapshot into the fulfillment record at the time of submission, so later changes to the profile don't affect existing fulfillments.

## Account Deletion

Players can delete their account from the profile page. This requires confirming their password. Account deletion is permanent and cannot be undone.

## Related Files

- `app/Http/Controllers/ProfileController.php` — The profile controller
- `resources/views/profile/edit.blade.php` — The profile edit page
- `resources/views/profile/partials/` — Partial views for update forms
- `routes/web.php` — Route: `/profile`
