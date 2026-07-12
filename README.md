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
| Backend | PHP 8.5+, Laravel 13 |
| Admin | Filament v5 |
| Frontend | Livewire 4, Alpine.js 3, Tailwind CSS 4, DaisyUI 5 |
| Chess | Chessground 9, Chess.js 1.4 |
| Database | MySQL 8 (local & production) |
| Payments | Stripe (sandbox mode for development) |
| Build | Vite 8 |
| Deployment | Docker (multi-stage), Coolify, Traefik, Let's Encrypt SSL |

---

## Getting Started

> **Local/prod parity:** Local development runs the **same Docker image** as
> production (`php:8.5-fpm` + nginx + supervisord, same PHP extensions, same
> `docker/php/php.ini`, same MySQL 8). The `dev` stage of the `Dockerfile` adds
> Node and a Vite dev server + queue worker. No Laragon/PHP-on-host required.

### Prerequisites
- [Docker](https://www.docker.com/) (Docker Desktop on Windows/macOS)
- Optional: PHP 8.5+ on the host only if you want to run `artisan`/`composer`
  outside the container

### Installation & run

```bash
git clone https://github.com/salehuddin/Chess-Puzzle-Challenge.git
cd Chess-Puzzle-Challenge

cp .env.example .env

# Build the dev image and start the app + MySQL 8 + Vite + queue worker.
docker compose up -d --build

# Generate APP_KEY and run migrations inside the container.
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan storage:link

# Seed roles + settings (required for admin access).
docker compose exec app php artisan db:seed --class=RolesSeeder --force
docker compose exec app php artisan db:seed --class=SettingsSeeder --force
```

Open http://localhost:8080. Vite HMR runs on http://localhost:5173 (configured
automatically — edit any Blade/JS/CSS file and the browser updates).

### Common commands

```bash
# All PHP/composer/npm commands run inside the container (Linux deps = prod parity):
docker compose exec app php artisan <command>
docker compose exec app composer <command>
docker compose exec app npm <command>

# Tail logs (nginx + php-fpm + vite + queue, all in one stream):
docker compose logs -f app

# Stop / start / rebuild after changing the Dockerfile or php.ini:
docker compose down
docker compose up -d --build
```

> If you prefer the native (non-Docker) workflow, Laragon/Valet/`php artisan
> serve` still work — set `DB_HOST=127.0.0.1` in `.env` and run
> `composer dev`. Note this loses Docker/prod parity (different PHP version,
> extensions, and php.ini upload limits).

### Queue worker (for CSV imports)

In Docker dev, a queue worker runs automatically under supervisord (see the
`[program:queue]` block in the `Dockerfile` `dev` stage) — no manual command
needed. For the native workflow: `php artisan queue:work`.

### Importing Lichess puzzles

1. Place the uncompressed Lichess CSV at `storage/app/lichess_db_puzzle.csv`.
2. In the admin panel, go to **Puzzles -> Import CSV Data**.
3. Set rating/popularity/theme filters and a row limit, then dispatch the import job.

---

## Project Documentation

- [`CPC-PRD.md`](CPC-PRD.md) — Product Requirements Document.
- [`dev-plan.md`](dev-plan.md) — Detailed development plan, milestones, and domain model.
- [`ROADMAP.md`](ROADMAP.md) — Lightweight progress board (Done / In Progress / Backlog).
- [`DEPLOYMENT.md`](DEPLOYMENT.md) — VPS + Coolify deployment guide.
- [`DEPLOYMENT-CLOUD-HOSTING.md`](DEPLOYMENT-CLOUD-HOSTING.md) — Hostinger Cloud Hosting guide (staging).

---

## Production Deployment

The app is deployed to production via **Coolify** on a VPS:

| Item | Value |
| :--- | :--- |
| **URL** | https://chesspuzzlechallenge.com |
| **Build** | Dockerfile (multi-stage: `php:8.5-fpm` + nginx + supervisord) |
| **Database** | MySQL 8 (Coolify-managed) |
| **SSL** | Let's Encrypt (auto via Traefik) |
| **CI/CD** | Auto-deploy on push to `main` |

See `deploy-coolify.ps1` for the automation script that provisions resources via the Coolify API.

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

Note: The test suite requires PHP 8.5+. Some environment setups may need to upgrade their CLI PHP to run PHPUnit.

---

## License

Proprietary. All rights reserved.
