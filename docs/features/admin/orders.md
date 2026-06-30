# Order Management

Orders represent purchases made by players. An order contains one or more items (challenges or bundles), tracks payment status, and links to the resulting enrollments and fulfillments.

## Order List

The table shows all orders:

- **Order #** — The order ID. Searchable.
- **User** — The buyer's name. Searchable.
- **Status** — Badge showing payment state: Pending (gray), Paid (green), Failed (red), Refunded (yellow).
- **Currency** — USD or MYR.
- **Total Amount** — The order total, displayed in the order's currency.
- **Payment Provider** — e.g. `stripe`, `sandbox`.
- **Payment Intent ID** — The provider's transaction reference. Hidden by default.
- **Paid At** — When payment was confirmed.

### Filters

- **Status** — Filter by Pending, Paid, Failed, or Refunded.

### Row Actions

- **Edit** — Opens the order edit form.
- **Enrollments** — Jumps to the enrollments list filtered by this order.
- **Fulfillments** — Jumps to the fulfillments list filtered by this order.

## Editing an Order

### Order Items (Create Only)

When creating a new order, you can add one or more items:

- **Type** — Challenge or Bundle.
- **Challenge/Bundle** — Select from active challenges or bundles. Price auto-fills.
- **Price** — Read-only, derived from the selected item.

### Order Details

- **User** — Who placed the order. Searchable.
- **Status** — Pending, Paid, Failed, or Refunded.
- **Currency** — 3-letter currency code (USD, MYR).
- **Subtotal / Discount / Total** — Financial breakdown.
- **Payment Provider** — The payment gateway used.
- **Payment Intent ID** — The transaction ID from the payment provider.
- **Paid At** — Timestamp of successful payment.
- **Metadata** — Key-value pairs for any extra data (e.g. payment gateway response).

### Linked Records (Read-Only)

- **Enrollments** — Lists all enrollments created from this order, with challenge names and status.
- **Fulfillments** — Lists all fulfillments linked to this order's enrollments.

## How Orders Are Created

Orders are created automatically during checkout when a user purchases a challenge or bundle. Admin users can also create orders manually. The typical lifecycle:

1. **Pending** — Order created, awaiting payment.
2. **Paid** — Payment confirmed. Enrollments are generated automatically.
3. **Failed** — Payment failed or was declined.
4. **Refunded** — Payment was reversed.

## Related Files

- `app/Filament/Resources/Orders/OrderResource.php` — Resource definition
- `app/Filament/Resources/Orders/Tables/OrdersTable.php` — List table
- `app/Filament/Resources/Orders/Schemas/OrderForm.php` — Create/edit form
- `app/Filament/Resources/Orders/Pages/EditOrder.php` — Edit page
