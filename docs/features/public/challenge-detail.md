# Challenge Detail Page

The challenge detail page shows everything about a specific challenge — what it is, what's included, and how to enroll.

## Sections

### Hero

A full-width banner with:
- **Poster image** — As the background (if uploaded).
- **Difficulty badge** — Beginner, Intermediate, Advanced, or Challenge.
- **Puzzle count** and **order label** (Sequential or Free order).
- **Challenge name** in large text.
- **Description** — The short summary.
- **Medal artwork** — The medal design image (if uploaded), displayed on the right side on larger screens.

### Info Cards

Three cards showing:
- **Puzzles** — Total puzzle count.
- **Rules** — Puzzle order (Sequential or Free) and time limit.
- **Price** — MYR and USD pricing.

### Enrollment Section

A prominent call-to-action box with:
- **Sticker artwork** — The sticker design (if uploaded), shown next to the enrollment text.
- **Status-aware messaging** — Different text depending on whether the user is enrolled, active, completed, or not logged in.
- **Action buttons** — Register to Enroll, Sign In, Continue Playing, Complete Payment, or View Details.

### Videos

If the challenge has videos added in the Content tab, they're displayed in an embedded player section.

### Image Gallery

Additional images uploaded in the Content tab, shown in a responsive grid.

### Medal Gallery

Medal images uploaded in the Content tab, shown in a separate gallery section.

### Challenge Details

The rich content from the Content tab's block editor. If no content blocks are set, falls back to the description text.

### Terms & Conditions

A collapsible section showing the challenge's terms and conditions (if set in the Content tab).

## Related Files

- `app/Livewire/ChallengeShow.php` — The Livewire component
- `resources/views/livewire/challenge-show.blade.php` — The template
- `routes/web.php` — Route: `/challenges/{challenge}`
