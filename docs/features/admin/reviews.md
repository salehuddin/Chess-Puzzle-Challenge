# Review Management

Reviews are player-submitted feedback collected at the moment of challenge completion. When a player solves the final puzzle, a `Review` record is born as `pending` alongside the sticker and fulfillment records. The player then rates the puzzle and the platform using chess-piece icons (pawn→queen), optionally leaves written feedback, and submits — flipping the review to `submitted`. Admins and editors moderate which reviews appear publicly on the landing page testimonials section.

## Access Control

- **Super Admin** — full access: list, view, edit, delete.
- **Editor** — list, view, edit (no delete).
- **Fulfillment staff** — denied (reviews are content moderation, not operations).
- Reviews cannot be created from the admin — they only originate from the player completion flow.

## Review List

The table shows all review records, sorted newest-first by `submitted_at`:

- **ID** — The review record ID.
- **Challenge** — Which challenge was reviewed. Searchable, sortable.
- **Player** — The reviewer's name. Searchable, sortable.
- **Puzzle** — Per-puzzle rating as a colored badge (1–5). Tooltip shows the chess-piece name (e.g. "Queen — 5 of 5"). Color: green (4–5), yellow (3), red (1–2).
- **Platform** — Overall CPC platform rating, same badge format as Puzzle.
- **Headline** — The player's optional title. Truncated to 50 chars. Hidden by default.
- **Feedback** — The player's optional body text. Truncated to 80 chars. Hidden by default.
- **Status** — Badge: Pending (gray) or Submitted (success).
- **Public** — Boolean icon indicating whether the review is shown on the public landing page.
- **Featured** — Boolean icon indicating admin-curated spotlight placement.
- **Submitted At** — When the player submitted the review. Pending reviews show "—".

### Filters

- **Status** — Pending or Submitted.
- **Public** — Ternary filter (Yes / No / Any).
- **Featured** — Ternary filter (Yes / No / Any).
- **Challenge** — Filter by challenge. Searchable, preloaded.

### Row Actions

- **Edit** — Opens the review edit form.
- **Enrollment** — Jumps to the linked enrollment record.

## Editing a Review

### Review Content (Read-Only)

- **Player** — The reviewer's name.
- **Challenge** — Which challenge was reviewed.
- **Submitted At** — When the player submitted (or null if still pending).
- **Puzzle Rating** — Formatted as `Queen (5/5)` or "Not rated".
- **Platform Rating** — Same format.

### Feedback (Editable)

- **Headline** — The player's optional title (max 120 chars).
- **Player Feedback** — The player's optional body text (max 2000 chars).

### Moderation

- **Status** — Pending or Submitted. Usually only flipped by the player flow, but editable for edge cases.
- **Show on testimonials section** (`is_public`) — When on, this review may appear in the public landing page testimonials.
- **Feature prominently** (`is_featured`) — Featured reviews are surfaced first on the landing page. Implies public visibility.

## Review Lifecycle

1. **Pending** — Review created on challenge completion. No ratings yet.
2. **Submitted** — Player selected ratings + optional feedback. `submitted_at` stamped. Awaiting moderation.

## Moderation Workflow

1. Player submits a review → status becomes `submitted`, `is_public=false` by default.
2. Admin/Editor reviews the feedback in the Filament resource.
3. To surface on the landing page: toggle `is_public=true`.
4. To spotlight: toggle `is_featured=true` (auto-implies public).
5. To remove from public view: toggle `is_public=false`.
6. To delete entirely: super_admin only (via the Edit page header action).

## Related Files

- `app/Filament/Resources/Reviews/ReviewResource.php` — Resource definition
- `app/Filament/Resources/Reviews/Tables/ReviewsTable.php` — List table
- `app/Filament/Resources/Reviews/Schemas/ReviewForm.php` — Edit form
- `app/Policies/ReviewPolicy.php` — Role-based access (admin + editor only)
- `app/Models/Review.php` — Model with scopes: `submitted()`, `public()`, `featured()`
