# User Dashboard

The user dashboard is the main hub for logged-in players. It shows all their challenge enrollments, progress, earned stickers, and order history in one place.

## Tabs

The dashboard has three tabs:

### My Challenges

Shows all enrolled challenges as cards, grouped by status:

- **Pending** — Orders created but not yet paid. The player needs to complete payment to start.
- **Active** — Challenges currently in progress. Shows a progress bar with solved/total puzzles and a "Continue Playing" button.
- **Completed** — Challenges fully solved. Shows completion date, medal shipment status (if applicable), and tracking link (if available).

Each challenge card displays:
- Challenge name and difficulty badge
- Progress (solved puzzles / total puzzles)
- Status badge (Active, Completed, Shipped, Pending Payment)
- Action button (Continue Playing, Complete Payment, View Details)
- Medal request status (if the medal is pending shipment)

### Collection

A catalog of all active challenges. Each challenge shows:
- Name and difficulty level
- Whether the player has earned the sticker (completed the challenge)
- A link to the challenge detail page

This tab helps players discover new challenges to enroll in.

### Orders

A list of all orders placed by the player:
- Order ID and date
- Status (Pending, Paid, Failed, Refunded)
- Total amount and currency
- Linked enrollments and their statuses

## Related Files

- `app/Livewire/Dashboard.php` — The Livewire component
- `resources/views/livewire/dashboard.blade.php` — The dashboard template
- `resources/views/dashboard.blade.php` — The page wrapper
- `routes/web.php` — Route definition
