# Checkout

The checkout page handles payment for challenge and bundle purchases. It's where players enter their payment details and complete their order.

## Flow

1. A player clicks "Enroll Now" on a challenge or "Buy Bundle" on a bundle.
2. If not logged in, they're redirected to register or log in first.
3. An order is created with status `pending`.
4. The player lands on the checkout page showing their order summary.
5. They complete payment via Stripe (or sandbox mode if enabled).
6. On success, the order status changes to `paid` and enrollments are generated.

## Payment Methods

- **Stripe** — Real card processing. Requires Stripe to be configured in Settings.
- **Sandbox Mode** — A dummy "Pay" button for development and demos. No real money is charged. Enabled/disabled in Settings → Payments.

## Order Summary

The checkout page shows:
- The items being purchased (challenge name or bundle name).
- The total amount in the order's currency (USD or MYR).
- Payment status.

## After Payment

Once payment is confirmed:
1. Order status → `paid`.
2. Enrollment records are created for each challenge in the order.
3. If it's a bundle, enrollments are created for all challenges in the bundle.
4. The player is redirected to their dashboard or order tracking page.

## Related Files

- `app/Http/Controllers/CheckoutController.php` — The checkout controller
- `resources/views/checkout/show.blade.php` — The checkout template
- `routes/web.php` — Route: `/checkout/{order}`
