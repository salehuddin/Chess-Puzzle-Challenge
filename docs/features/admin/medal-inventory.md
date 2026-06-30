# Medal Inventory

The Medal Inventory page tracks physical medal stock across all challenges. It's the central place to monitor stock levels, restock medals, and audit inventory movements.

## Inventory Table

The table shows every challenge with its medal stock data, sorted by stock level (lowest first):

- **SKU** — The challenge SKU (e.g. `CHAL-00001`).
- **Challenge** — The challenge name.
- **On Hand** — Physical medals currently in the warehouse.
- **Reserved** — Medals allocated to fulfillments that are ready to ship but not yet dispatched.
- **Available** — On Hand minus Reserved. This is the actual stock available for new orders.
- **Threshold** — The reorder trigger point. Hidden by default.
- **Status** — A badge showing: OK (green), Low Stock (yellow), or Out of Stock (red).

### Filters

- **Low Stock** — Shows only challenges where available stock is at or below the reorder threshold.
- **Out of Stock** — Shows only challenges with zero available medals.

## Actions

### Restock

Click the **Restock** action on any row to add new medals:

- **Restock Quantity** — How many medals are being added.
- **Note** — Optional field for supplier name, PO number, or other reference.

This increments the stock on hand and logs a `restock` movement with the quantity, note, and who performed it.

### Adjust Stock

Click **Adjust Stock** to set an absolute stock count:

- **New Stock On Hand** — The new total (not a delta). Pre-filled with the current count.
- **Reason** — Optional field for the adjustment reason (cycle count, damaged units, etc.).

This overwrites the stock on hand and logs an `adjustment` movement with the difference. Use this for corrections after physical counts.

### View Movements

Click **View Movements** to see the stock movement audit trail for a challenge:

- A modal shows the last 10, 25, or 50 movements.
- Each movement shows: date, type (restock/adjustment), quantity change, resulting balance, who made the change, and any notes.

## How Stock Works

- **On Hand** — The raw count of physical medals in the warehouse. You control this via Restock and Adjust.
- **Reserved** — Computed automatically. Counts fulfillments with status `ready_to_ship` for that challenge.
- **Available** — Computed: `On Hand - Reserved`. This is what matters for new orders.
- **Threshold** — Set per challenge on the Challenge Details page. When Available drops to or below this number, the Dashboard shows a low stock warning.

## Related Files

- `app/Filament/Pages/MedalInventory.php` — The inventory page
- `app/Services/MedalInventoryService.php` — Business logic for restock, adjust, and movement logging
- `resources/views/filament/pages/medal-inventory.blade.php` — The blade template
- `resources/views/filament/partials/medal-stock-movements.blade.php` — Movement history modal content
