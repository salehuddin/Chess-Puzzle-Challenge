# Chess Puzzle Challenge — Development Roadmap

A lightweight project board for tracking features, improvements, and bugs.

## Status

- **Current focus:** Production deployment complete. Next: admin user setup, Stripe integration, automated tests.
- **Environment:** Local Laragon development + production VPS via Coolify.
- **Production URL:** https://chesspuzzlechallenge.com
- **Version control:** Git, pushed to https://github.com/salehuddin/Chess-Puzzle-Challenge

## Done

- [x] Admin puzzles list with dynamic theme filter (themes derived from actual puzzles).
- [x] Bulk action on puzzles list: attach selected puzzles to a challenge via challenge selector.
- [x] Attach Puzzles page uses the same dynamic theme filter for consistency.
- [x] Duplicate `lichess_id` prevention:
  - [x] Unique validation on the manual puzzle form.
  - [x] CSV imports skip duplicates and report imported vs. skipped counts.
- [x] Replace text hint with client-side visual hint:
  - [x] Highlight the piece to move on the board.
  - [x] Track hint clicks client-side.
  - [x] Drop the `hint` database column.
- [x] Production VPS deployment via Coolify (June 30, 2026):
  - [x] Dockerfile (multi-stage: PHP 8.5 + nginx + supervisord).
  - [x] MySQL 8 database provisioned and connected.
  - [x] Traefik reverse proxy with auto Let's Encrypt SSL.
  - [x] Auto-deploy on push to `main` branch.
  - [x] Site live at https://chesspuzzlechallenge.com.
- [x] Filament `FileUpload` and Editor.js image uploads working in production (July 11, 2026):
  - [x] Dedicated `livewire-tmp` disk in `config/filesystems.php` (was failing with `Path must not be empty` because the default `local` disk root conflicted with PHP's temp-file lifecycle on Windows + Linux under a Coolify persistent volume).
  - [x] Custom `docker/php/php.ini` to raise `upload_max_filesize=25M`, `post_max_size=30M` (php:8.5-fpm base image ships with 2M/8M).
  - [x] `docker/entrypoint.sh` to create `livewire-tmp`, framework, log, and cache directories on every container start (Coolify mounts the persistent volume over `/app/storage/app`, masking anything created at image-build time).
  - [x] `bootstrap/app.php` trusts all proxies and `AppServiceProvider` forces HTTPS in production, fixing the Livewire signed-URL 401 caused by `APP_URL` scheme mismatch behind Traefik.
  - [x] `bootstrap/app.php` exception handler returns JSON for `livewire/upload-file` errors so 500s are diagnosable from the browser DevTools without server log access.
  - [x] Removed leftover `public/test_upload.php` debug file.
- [x] Player review flow on challenge completion (July 18, 2026):
  - [x] Confetti celebration + animated stats card on the completion screen (puzzles solved, difficulty band, sticker earned).
  - [x] Chess-piece rating selector (pawn → queen, 1–5) for per-puzzle and overall platform ratings.
  - [x] Inline review card with optional headline + free-form feedback textarea, submitted via Livewire.
  - [x] `reviews` table (one-to-one with `enrollments`, mirrors `fulfillments` pattern), `Review` model with `pending`/`submitted` status lifecycle.
  - [x] Social share buttons (copy link, X, Facebook, WhatsApp) revealed after submit.
  - [x] `ReviewPolicy` gates moderation to `super_admin` + `editor`; fulfillment staff denied.
- [x] Reviews moderation UI in admin (July 18, 2026):
  - [x] Filament `ReviewResource` (List + Edit pages, no Create — reviews are born from the player flow).
  - [x] Table columns: chess-piece rating badges with tooltips, status badge, public/featured toggles, default sort newest first.
  - [x] Filters: status, is_public (ternary), is_featured (ternary), challenge.
  - [x] Moderation form with `is_public` / `is_featured` toggles powering the landing-page testimonials section.

## In Progress

- [ ] Admin user creation on production (via Coolify terminal).
- [ ] Queue worker configuration for CSV import jobs.

## Backlog

- [x] Initialize a Git repository and push to GitHub.
- [x] Add CI workflow (GitHub Actions) and deployment guide (Coolify/Hostinger).
- [x] Set up a staging/preview VPS for team testing.
- [ ] Persist hint-click counter server-side for leaderboards / bragging rights.
- [ ] Add admin feature tests for puzzle import, filtering, and bulk actions.
- [ ] Optimize theme filter query for very large puzzle datasets if needed.
- [ ] Stripe payment integration (FPX + cards).
- [ ] GeoIP-based PPP pricing (MYR/USD).
- [ ] Upload Lichess CSV to production for puzzle data.
- [ ] Replace placeholder testimonials copy in `landing/sections/11-testimonials.blade.php` with DB-backed query of approved reviews.
- [ ] Add "pending review" nudge banner on the dashboard (mirrors the existing pending-medal banner).
- [ ] Track elapsed time + hints used per challenge (new `enrollments.started_at` column + JS state persistence).

## Notes

- Puzzle solution moves are used for the visual hint instead of a live engine, because the puzzle already knows the correct answer. A real Stockfish integration is listed in the backlog as an optional enhancement.
- Production runs PHP 8.5 (matching local development via Laragon). The composer.lock requires PHP >=8.4.1 (Symfony 8.1 components).
- The deployment uses a custom Dockerfile (not Nixpacks) because Nixpacks' PHP provider had version mismatch issues with the composer.lock.
- **Filament file uploads require four pieces to be in sync in production** (see July 11, 2026 entry above): the `livewire-tmp` disk in `config/filesystems.php`, the `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` env var in Coolify, the custom `docker/php/php.ini`, and the `docker/entrypoint.sh`. If any of these regresses, uploads will fail. CI should fail if any of the four files is missing.
