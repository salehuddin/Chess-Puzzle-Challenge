# Challenges Listing

The Browse Challenges page shows all available challenges and bundles for players to explore and enroll in.

## Challenge Cards

Each challenge card displays:
- **Poster image** — If uploaded, shown as the card header. Otherwise, a chess pattern placeholder is shown.
- **Difficulty badge** — Beginner, Intermediate, Advanced, or Challenge (auto-detected from the name).
- **Name** — The challenge title.
- **Description** — A short summary of the challenge.
- **Puzzle count** — How many puzzles are in the challenge.
- **Medal badge** — Indicates a physical medal reward.
- **Pricing** — MYR (primary) and USD.
- **Enrollment status** — If the logged-in user is enrolled, they see a status badge (In Progress, Completed, Payment Pending) and a contextual button (Continue Playing, View Details, Complete Payment).

## Bundle Cards

Bundles appear below the challenges section with:
- A "Best Value" badge.
- Bundle name and description.
- List of included challenges (with ownership indicators).
- Pricing and a "Buy Bundle" button.

## Filters

A row of filter buttons at the top:
- **All challenges** — Shows everything.
- **Beginner** — Filters challenges with "beginner" in the name.
- **Intermediate** — Filters challenges with "intermediate" in the name.
- **Advanced** — Filters challenges with "advanced" in the name.

## Related Files

- `app/Livewire/ChallengeIndex.php` — The Livewire component
- `resources/views/livewire/challenge-index.blade.php` — The template
- `routes/web.php` — Route: `/challenges`
