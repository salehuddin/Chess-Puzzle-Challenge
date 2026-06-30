# Order Tracking

The order tracking page lets players view the status of their orders and track medal shipments.

## What It Shows

For each order:
- **Order ID** and date
- **Status** — Pending, Paid, Failed, or Refunded
- **Items** — Which challenges or bundles were purchased
- **Enrollment status** — Whether each challenge enrollment is active or completed
- **Fulfillment status** — Medal shipping progress (if applicable)
- **Tracking link** — Clickable link to the courier's tracking page (when available)

## When It's Used

- After purchasing a challenge, players can check if their payment went through.
- After completing a challenge, players can track their medal shipment.
- If a payment is pending, players can find the link to complete it.

## Related Files

- `app/Livewire/OrderTracking.php` — The Livewire component
- `resources/views/livewire/order-tracking.blade.php` — The template
- `routes/web.php` — Route: `/orders/{enrollment}/track`
