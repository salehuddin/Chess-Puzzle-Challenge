# Chess Puzzle Challenge

A platform that bridges digital chess mastery and physical rewards. Players solve curated Lichess puzzle challenges and earn a digital sticker plus a custom-designed physical medal delivered by mail.

Built with Laravel 13, Filament v5, Livewire 4, Alpine.js, and Tailwind CSS v4.

---

## Features

### Gameplay
- Interactive board powered by **Chessground** (UI) and **Chess.js** (move validation).
- Solve puzzles sequentially with progress saved to `localStorage` (resume after refresh).
- Undo and reset without penalty.
- Client-side visual hint: highlights the piece to move, with a per-position hint counter.
- "Proof of Work" completion token (session-based) prevents simple API spoofing.

### Challenges & Bundles
- Admins curate themed challenge series (e.g., *Ultimate Winter 2026*) from the Lichess puzzle database.
- Flexible per-challenge rules, pricing (USD/MYR), and medal artwork.
- Bundles group multiple challenges with custom pricing; medals ship individually as each challenge is completed.

### Commerce & Fulfillment
- Orders, order items, enrollments, and fulfillments domain model.
- Sandbox payment mode for local development; Stripe integration point for production (FPX + cards).
- Address snapshotting at enrollment for accurate delivery.
- Admin fulfillment queue with status guardrails (ready_to_ship -> shipped -> delivered).
- Courier tracking URLs surfaced to the user's dashboard.

### Admin (Filament)
- Dashboard with business KPIs and fulfillment snapshot widgets.
- Resources for Challenges, Puzzles, Users, Orders, Enrollments, Bundles, and Fulfillments.
- CSV puzzle import pipeline that streams multi-GB Lichess CSVs into the database via queue jobs.
- CSV Puzzle Explorer for browsing, filtering, and importing curated subsets.
- Duplicate `lichess_id` prevention on manual entry and CSV import.
- Dynamic theme filters derived from the puzzles actually in the database.
- Bulk attach puzzles to a challenge with a challenge selector.

---

## Tech Stack

| Layer | Technology |
| :--- | :--- |
| Backend | PHP 8.3+, Laravel 13 |
| Admin | Filament v5 |
| Frontend | Livewire 4, Alpine.js 3, Tailwind CSS 4, DaisyUI 5 |
| Chess | Chessground 9, Chess.js 1.4 |
| Database | SQLite (local), MySQL/Postgres (production) |
| Payments | Stripe (sandbox mode for development) |
| Build | Vite 8 |

---

## Getting Started

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 18+ and npm
- A database (SQLite works for local development)

### Installation

```bash
git clone https://github.com/salehuddin/Chess-Puzzle-Challenge.git
cd Chess-Puzzle-Challenge
composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate
npm run build
```

### Run locally

```bash
php artisan serve
```

Or use Laragon / Valet / Sail depending on your environment.

### Queue worker (for CSV imports)

```bash
php artisan queue:work
```

### Importing Lichess puzzles

1. Place the uncompressed Lichess CSV at `storage/app/lichess_db_puzzle.csv`.
2. In the admin panel, go to **Puzzles -> Import CSV Data**.
3. Set rating/popularity/theme filters and a row limit, then dispatch the import job.

---

## Project Documentation

- [`CPC-PRD.md`](CPC-PRD.md) — Product Requirements Document.
- [`dev-plan.md`](dev-plan.md) — Detailed development plan, milestones, and domain model.
- [`ROADMAP.md`](ROADMAP.md) — Lightweight progress board (Done / In Progress / Backlog).

---

## Domain Model

```
Challenge ────< Puzzle (via challenge_puzzle pivot, sequence)
    │
    ├── Bundle (via bundle_challenge pivot, sort_order)
    │
    └── Enrollment (user_id + challenge_id, unique)
            │  status: active -> completed
            │
            ├── PuzzleProgress (user_id + challenge_id + puzzle_id)
            │     solved_at
            │
            └── Fulfillment (enrollment_id)
                  status: pending -> ready_to_ship -> shipped -> delivered
                  address_snapshot, courier, tracking_number, tracking_url

Order (user_id)
  status: pending -> paid -> failed/refunded
  └── OrderItem (polymorphic item_type + item_id)
        └── Enrollment (via order_item_id)

Sticker (user_id + challenge_id)
  unlocked_at — earned on challenge completion
```

---

## Testing

```bash
php artisan test
```

Note: The test suite requires PHP 8.3+. Some environment setups may need to upgrade their CLI PHP to run PHPUnit.

---

## License

Proprietary. All rights reserved.
