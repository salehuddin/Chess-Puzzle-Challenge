# Challenge Enrollment

The challenge enrollment flow handles creating an order and enrollment when a player decides to join a challenge.

## Flow

1. Player clicks "Enroll Now" on a challenge detail page.
2. If not logged in, they're redirected to register (with a redirect back to the enrollment endpoint).
3. If already enrolled, they're redirected to the puzzle player (if active) or order tracking (if pending).
4. An order is created:
   - **Admin users** — Order is created with status `paid` (instant enrollment, no payment needed).
   - **Regular users** — Order is created with status `pending`.
5. An enrollment is created linked to the order item.
6. If sandbox payment mode is enabled, the player is redirected to the checkout page.
7. Otherwise, the player is redirected to their dashboard.

## Admin Enrollment

Admin users bypass payment entirely. When an admin clicks "Enroll Now":
- The order is created as `paid`.
- The enrollment is created as `active`.
- The player is redirected directly to the puzzle player.

## Duplicate Prevention

If a player tries to enroll in a challenge they're already enrolled in:
- **Active enrollment** → Redirected to the puzzle player.
- **Other statuses** → Redirected to order tracking.

## Related Files

- `app/Http/Controllers/ChallengeEnrollmentController.php` — The enrollment controller
- `routes/web.php` — Route: `/challenges/{challenge}/enroll`
