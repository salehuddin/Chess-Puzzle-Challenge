# Enrollment Management

An enrollment represents a player's participation in a challenge. It's created when a user enrolls (either by purchasing or via admin enrollment) and tracks their status from active to completed.

## Enrollment List

The table shows all enrollments across all challenges:

- **ID** — The enrollment record ID.
- **User** — The player's name. Searchable.
- **Challenge** — Which challenge they're enrolled in. Searchable.
- **Order #** — The linked order ID. Click to jump to the order.
- **Fulfillment** — Current fulfillment status (Pending, Ready to Ship, Shipped, Delivered). Shows "Not created" if no fulfillment exists yet.
- **Status** — `Active` (in progress) or `Completed` (all puzzles solved).
- **Activated At / Completed At** — Timestamps for when the enrollment started and finished.

### Filters

- **Status** — Filter by Active or Completed enrollments.

### Row Actions

- **Edit** — Opens the enrollment edit form.
- **Order** — Jumps to the linked order (if one exists).
- **Fulfillment** — Jumps to the linked fulfillment record (if one exists).

## Editing an Enrollment

The edit form has two sections:

### Enrollment

- **User** — Which player this enrollment belongs to. Searchable.
- **Challenge** — Which challenge they're enrolled in. Searchable.
- **Order Item** — Links to a specific order item. When you select an order item, the user and challenge are auto-filled from the order.
- **Status** — Active or Completed.
- **Activated At** — When the enrollment became active.
- **Completed At** — When the player finished all puzzles.

### Linked Records

Read-only summaries showing:

- **Order** — The associated order number, player name, and order status.
- **Fulfillment** — The fulfillment ID and its current status.

## How Enrollments Are Created

Enrollments are created automatically when:

1. A user completes checkout and payment for a challenge or bundle.
2. An admin enrolls a user directly (admin enrollment bypasses payment).

The enrollment starts as `active` and transitions to `completed` when the player solves all puzzles in the challenge.

## Related Files

- `app/Filament/Resources/Enrollments/EnrollmentResource.php` — Resource definition
- `app/Filament/Resources/Enrollments/Tables/EnrollmentsTable.php` — List table
- `app/Filament/Resources/Enrollments/Schemas/EnrollmentForm.php` — Edit form
- `app/Filament/Resources/Enrollments/Pages/EditEnrollment.php` — Edit page
