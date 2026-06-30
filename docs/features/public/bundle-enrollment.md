# Bundle Enrollment

The bundle enrollment flow handles purchasing a bundle, which includes multiple challenges at a discounted price.

## Flow

1. Player clicks "Buy Bundle" on the Browse Challenges page.
2. If not logged in, they're redirected to register (with a redirect back).
3. An order is created with the bundle's price:
   - **Admin users** — Order is created as `paid` (instant enrollment).
   - **Regular users** — Order is created as `pending`.
4. The `CommerceHierarchyService` creates order items and enrollments for every challenge in the bundle.
5. If sandbox payment mode is enabled, the player is redirected to checkout.
6. Otherwise, they're redirected to their dashboard.

## What Happens After Payment

When the order status changes to `paid`:
- Enrollment records are created for each challenge in the bundle.
- Each enrollment starts as `active`.
- The player can begin solving puzzles in any of the bundled challenges.

## Related Files

- `app/Http/Controllers/BundleEnrollmentController.php` — The enrollment controller
- `routes/web.php` — Route: `/bundles/{bundle}/enroll`
