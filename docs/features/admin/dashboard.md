# Admin Dashboard

The dashboard is the first screen you see after logging into the admin panel. It gives you a real-time snapshot of how the business is performing without needing to dig into individual pages.

## What You See

### Business KPI Overview

Nine stat cards arranged across the top of the page:

- **Paid Orders** — Total number of orders with successful payment. Includes a 7-day sparkline chart so you can spot trends at a glance.
- **Active Enrollments** — Users who are currently working through a challenge (not yet completed).
- **Ready To Ship** — Completed enrollments where the medal is packed and waiting for dispatch.
- **Shipped** — Medals that have been sent out to players.
- **Pending Payment** — Orders that were created but not yet paid. These may need follow-up if they've been sitting for a while.
- **Paid Revenue (USD)** — Total revenue from paid USD orders.
- **7-Day New Paid Orders** — How many orders came in over the trailing 7 days, with the same sparkline chart.
- **Low Stock SKUs** — Challenges where available medal inventory has dropped to or below the reorder threshold. Time to restock.
- **Out of Stock SKUs** — Challenges with zero medals available. These need immediate attention.

### Fulfillment Snapshot

A second row of cards focused on logistics:

- **Ready To Ship** — Fulfillments queued for dispatch.
- **Shipped Missing Tracking** — Medals that were marked as shipped but have no tracking number or URL. Use the Fulfillments page to add tracking details.
- **Missing Address Snapshot** — Fulfillments with an empty address on file. These can't be shipped until resolved.
- **Shipped (Last 7 Days)** — Recent throughput — how many medals went out in the past week.

## When To Use It

- **Morning check-in** — Glance at KPIs to see if anything needs urgent attention (out-of-stock medals, pending payments piling up).
- **Before shipping runs** — Check the Fulfillment Snapshot to know how many packages to prepare.
- **Weekly review** — Look at the 7-day trends to understand growth or slowdowns.

## Related Files

- `app/Filament/Pages/Dashboard.php` — Page layout and column configuration
- `app/Filament/Widgets/AdminKpiOverview.php` — Business KPI stat cards
- `app/Filament/Widgets/FulfillmentSnapshot.php` — Fulfillment logistics stat cards
