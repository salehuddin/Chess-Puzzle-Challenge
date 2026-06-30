# Fulfillment Management

Fulfillments track the physical shipping of medals to players. When a player completes a challenge, a fulfillment record is created to manage the medal dispatch process from packing through delivery.

## Fulfillment List

The table shows all fulfillment records:

- **ID** — The fulfillment record ID.
- **Order #** — The linked order number. Searchable.
- **Player** — The recipient's name. Searchable.
- **Challenge** — Which challenge medal is being shipped. Searchable.
- **Status** — Badge showing shipping state: Pending (gray), Ready to Ship (yellow), Shipped (blue), Delivered (green).
- **Courier** — The shipping carrier (e.g. DHL, FedEx).
- **Tracking Number** — The shipment tracking number. Hidden by default.
- **Tracking URL** — Clickable link to the carrier's tracking page. Hidden by default.
- **Shipped At / Delivered At** — Timestamps for when the medal was dispatched and received.

### Filters

- **Status** — Filter by Pending, Ready to Ship, Shipped, or Delivered.

### Row Actions

- **Edit** — Opens the fulfillment edit form.
- **Enrollment** — Jumps to the linked enrollment record.
- **Order** — Jumps to the linked order (if one exists).

## Editing a Fulfillment

### Fulfillment Details

- **Enrollment** — Which enrollment this fulfillment is for. Searchable. When selected, the player's address auto-fills.
- **Status** — Pending, Ready to Ship, Shipped, or Delivered.
- **Courier** — The shipping carrier name.
- **Tracking Number** — The shipment tracking number.
- **Tracking URL** — Link to the carrier's tracking page.
- **Shipped At** — When the medal was dispatched.
- **Delivered At** — When the medal was received.
- **Address Snapshot** — Key-value pairs of the shipping address. Auto-populated from the player's profile when the enrollment is selected.

### Linked Records (Read-Only)

- **Enrollment** — The enrollment details (player, challenge, status).
- **Order** — The associated order details.

## Fulfillment Lifecycle

1. **Pending** — Fulfillment created, waiting to be prepared.
2. **Ready to Ship** — Medal packed and ready for dispatch.
3. **Shipped** — Medal handed to the courier. Timestamp recorded automatically.
4. **Delivered** — Medal received by the player. Timestamp recorded automatically.

## Related Files

- `app/Filament/Resources/Fulfillments/FulfillmentResource.php` — Resource definition
- `app/Filament/Resources/Fulfillments/Tables/FulfillmentsTable.php` — List table
- `app/Filament/Resources/Fulfillments/Schemas/FulfillmentForm.php` — Edit form
