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

## Notes

- Puzzle solution moves are used for the visual hint instead of a live engine, because the puzzle already knows the correct answer. A real Stockfish integration is listed in the backlog as an optional enhancement.
- Production runs PHP 8.5 (matching local development via Laragon). The composer.lock requires PHP >=8.4.1 (Symfony 8.1 components).
- The deployment uses a custom Dockerfile (not Nixpacks) because Nixpacks' PHP provider had version mismatch issues with the composer.lock.
