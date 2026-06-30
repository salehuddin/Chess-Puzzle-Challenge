# User Management

The Users page lets you view and manage all registered players. Each user has a profile with personal details, shipping address, and a full history of their orders, enrollments, puzzle progress, and earned stickers.

## User List

The table shows all registered users with computed metrics:

- **Name** — The player's display name. Searchable and sortable.
- **Email** — Searchable and copyable.
- **Verified** — Whether their email is verified. Shows as a green or red badge.
- **Roles** — Assigned roles (e.g. admin). Hidden by default.
- **Paid Orders** — Total number of paid orders.
- **Spent (USD)** — Lifetime spending in USD.
- **Active** — Currently active enrollments (in progress).
- **Completed** — Fully completed challenges.
- **Country / City** — Location fields. Hidden by default.
- **Registered** — When they signed up.

### Filters

- **Email Verified** — Toggle between verified only, unverified only, or all users.
- **Country** — Filter by country. Searchable dropdown.
- **Has Paid Orders** — Show only users with or without paid orders.

### Row Actions

- **View** — Opens the user's profile overview.
- **Edit** — Opens the user edit form.
- **Orders** — Jumps to the user's orders tab.

## User Profile Tabs

Each user has seven sub-pages:

### Overview

A summary card showing the user's key stats: name, email, registration date, country, and a quick snapshot of their activity.

### Edit

Edit the user's profile:

- **Name / Email** — Basic account info.
- **Email Verified At** — Manually set or clear the verification timestamp.
- **Password** — Leave blank to keep the current password. Required when creating a new user.
- **Address** — Shipping address fields (address line 1 & 2, city, state, postcode, country). Used for medal fulfillment.

### Orders

A table of all orders placed by this user, with status, currency, amount, and payment details.

### Enrollments

All challenge enrollments for this user, showing which challenges they're enrolled in, their status (active/completed), and linked fulfillment status.

### Puzzle Progress

Detailed puzzle-by-puzzle progress for this user across all challenges. Shows which puzzles they've solved and when.

### Medals (Stickers)

A gallery of stickers earned by completing challenges. Each sticker shows the challenge name and when it was earned.

### Activity Log

A timeline of actions performed by this user: enrollments, puzzle completions, orders, and other events.

## Creating a User

Admins can create users manually. Required fields:

- **Name** — Display name.
- **Email** — Must be unique.
- **Password** — Required for new users. Must meet the application's password requirements.

Optional: set the email verification timestamp and fill in the shipping address.

## Related Files

- `app/Filament/Resources/Users/UserResource.php` — Resource definition with sub-navigation
- `app/Filament/Resources/Users/Tables/UsersTable.php` — List table with computed metrics
- `app/Filament/Resources/Users/Schemas/UserForm.php` — Create/edit form
- `app/Filament/Resources/Users/Widgets/UserOverview.php` — Profile overview widget
- `app/Filament/Resources/Users/Widgets/UserActivityTimeline.php` — Activity timeline widget
