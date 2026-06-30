# Fulfillment Queue

The Fulfillment Queue is a dedicated operations page for managing medal shipments. It shows only fulfillments that need attention — those that are ready to ship or recently shipped — so the logistics team can focus on what needs to go out.

## What It Shows

The table displays fulfillments filtered to `Ready to Ship` and `Shipped` statuses:

- **Order #** — The linked order number.
- **Player** — The recipient's name and email (clickable to copy).
- **Challenge** — Which challenge's medal is being shipped. If the challenge has zero medal stock, a warning appears: "No medal stock available".
- **Status** — Ready to Ship (yellow) or Shipped (blue).
- **Courier** — The shipping carrier.
- **Tracking #** — The tracking number.
- **Tracking URL** — Clickable link to track the shipment.
- **Stock** — Current medal stock on hand for that challenge. Shows as a red badge when stock is zero.
- **Completed At** — When the player completed the challenge.
- **Shipped At** — When the medal was dispatched.

## Filters

- **Status** — Toggle between Ready to Ship and Shipped.
- **Completed Not Shipped** — Shows only fulfillments that are ready to ship (completed enrollment, not yet dispatched).
- **Shipped Missing Tracking** — Shows shipped fulfillments with no tracking number or URL. Use this to identify shipments that need tracking info added.

## Actions

### Per Row

- **Dispatch Shipment** — Queues a `ProcessCourierShipmentJob` for the fulfillment. Requires confirmation. Use this when a medal is packed and ready to hand off to the courier.
- **Open Fulfillment** — Opens the full fulfillment edit page where you can add tracking details, update status, etc.

### Bulk Actions

Select multiple rows using the checkboxes, then:

- **Dispatch Selected** — Queues courier jobs for all selected fulfillments at once. Requires confirmation.

## Workflow

1. A player completes a challenge → fulfillment is created with status `Pending`.
2. Admin changes status to `Ready to Ship` (via the Fulfillments page or Medal Status tab on the Challenge).
3. The fulfillment appears in this queue.
4. Admin packs the medal and clicks **Dispatch Shipment** (or updates tracking info on the Fulfillments page).
5. Status changes to `Shipped` and the row moves to the "shipped" section of the queue.

## Related Files

- `app/Filament/Pages/FulfillmentQueue.php` — The queue page
- `resources/views/filament/pages/fulfillment-queue.blade.php` — The blade template
- `app/Jobs/ProcessCourierShipmentJob.php` — The background job that processes courier dispatch
