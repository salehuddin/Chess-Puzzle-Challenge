# Challenge Management

Challenges are the core product. Each challenge is a series of chess puzzles that a player works through. Completing a challenge earns a physical medal shipped to the player's door. This page covers how to create, configure, and manage challenges in the admin panel.

## Creating a Challenge

1. Go to **Challenges** in the sidebar and click **New Challenge**.
2. Fill in the form:
   - **Name** — The display name shown to players (e.g. "Winter Beginner Series").
   - **Slug** — Auto-generated from the name. Used in URLs. Must be unique.
   - **SKU** — Auto-generated as `CHAL-00001`, `CHAL-00002`, etc. You can override it if needed.
   - **Description** — A short summary of the challenge. Appears on the Browse Challenges cards and the challenge detail page.
3. Click **Create**.

After creation, you'll land on the **Details** tab where you can configure the rest.

## Editing a Challenge

Each challenge has six sub-pages accessible via tabs at the top:

### Details

The main configuration page. Fields include:

- **Name / Slug / Status** — Core identity. Set status to `Published` when ready to go live.
- **Active** — Toggle to make the challenge visible on the public site. Inactive challenges are hidden from listings.
- **Puzzle Count** — Auto-calculated from attached puzzles. You can't edit this directly.
- **Pricing** — USD and MYR prices displayed on the storefront.
- **Meta Title / Meta Description** — SEO fields for the challenge page.
- **Description** — The short summary text.
- **Visual Assets** — Upload poster image, medal artwork, medal gallery images, and sticker artwork.
- **Medal / Fulfillment** — Physical dimensions (weight, length, width, height) used for shipping calculations.
- **Medal Inventory** — Stock on hand, reorder threshold, and reorder quantity for physical medals.

### Content

Rich content for the challenge detail page, managed with a block editor:

- **Content Blocks** — Headings, paragraphs, lists, and delimiters rendered on the public challenge page.
- **Image Gallery** — Additional images displayed in a grid below the main content.
- **Videos** — YouTube or Vimeo links embedded on the page.
- **Terms & Conditions** — Shown in a collapsible section at the bottom.

### Puzzles

A table of all puzzles attached to this challenge. Each puzzle has a Lichess ID, rating, themes, and a sequence number.

- **Attach Puzzles** — Opens a dedicated page to browse and add puzzles from the Lichess database.
- **Randomize Sequence** — Shuffles the puzzle order randomly.
- **Sort Rating ASC/DESC** — Orders puzzles by rating (easiest-first or hardest-first).
- **Drag to reorder** — Manually reorder puzzles by dragging rows.
- **Click a row** — Opens a chess board preview on the right side showing the puzzle position and solution moves.
- **Detach** — Remove individual puzzles or bulk-detach selected ones.

### Players

A list of all enrolled users with their progress:

- **Player name and email** — Searchable.
- **Challenge Start Date** — When they enrolled.
- **Status** — Active, completed, or pending.
- **Progress** — Shows solved count, total puzzles, and percentage (e.g. "45 / 100 (45%)").

### Medal Status

Tracks the fulfillment lifecycle for each completed player:

- **Player / Email** — Who it is.
- **Progress** — Completion percentage as a badge.
- **Address** — The shipping address on file.
- **Shipment Status** — Editable inline: Pending → Ready to Ship → Shipped → Delivered. Setting to Shipped auto-records the timestamp.
- **Courier / Tracking # / Tracking URL** — Editable inline. Fill these in when dispatching.
- **Filters** — Filter by shipment status or completion bucket (Almost Complete 80%+, Fully Complete 100%).

### Analytics

Performance metrics for the challenge with a configurable date range:

- **Sign Ups Chart** — A line chart showing daily enrollments over the selected period.
- **KPI Snapshot** — Total players, estimated USD revenue, completed count, and average puzzles completed per player.

## Challenge List Table

The main Challenges page shows a table with:

- Name, description, medal/sticker artwork thumbnails, pricing (USD/MYG), puzzle count, and active status.
- Columns are toggleable — click the column toggle to show/hide description, slug, or timestamps.
- Each row has a **Manage** dropdown with quick links to Details, Analytics, Content, Puzzles, Players, and Medal Status.
- Bulk delete is available via checkboxes.

## Related Files

- `app/Filament/Resources/Challenges/ChallengeResource.php` — Resource definition, routes, and sub-navigation
- `app/Filament/Resources/Challenges/Schemas/ChallengeForm.php` — Create form schema
- `app/Filament/Resources/Challenges/Pages/EditChallenge.php` — Details tab
- `app/Filament/Resources/Challenges/Pages/ChallengeContent.php` — Content tab
- `app/Filament/Resources/Challenges/Pages/ChallengePuzzles.php` — Puzzles tab
- `app/Filament/Resources/Challenges/Pages/ChallengePlayers.php` — Players tab
- `app/Filament/Resources/Challenges/Pages/ChallengeMedalStatus.php` — Medal Status tab
- `app/Filament/Resources/Challenges/Pages/ChallengeAnalytics.php` — Analytics tab
- `app/Filament/Resources/Challenges/Tables/ChallengesTable.php` — List table configuration
