# Chess Puzzle Challenge — Development Roadmap

A lightweight project board for tracking features, improvements, and bugs.

## Status

- **Current focus:** Admin puzzle management and puzzle player UX.
- **Environment:** Local Laragon development.
- **Version control:** Not yet initialized. A GitHub repository can be created later.

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

## In Progress

- [ ] This roadmap file.

## Backlog

- [ ] Initialize a Git repository and push to GitHub.
- [ ] Set up a staging/preview VPS for team testing.
- [ ] Add CI/CD pipeline (GitHub Actions) for tests and deployments.
- [ ] Persist hint-click counter server-side for leaderboards / bragging rights.
- [ ] Add admin feature tests for puzzle import, filtering, and bulk actions.
- [ ] Optimize theme filter query for very large puzzle datasets if needed.

## Notes

- Puzzle solution moves are used for the visual hint instead of a live engine, because the puzzle already knows the correct answer. A real Stockfish integration is listed in the backlog as an optional enhancement.
- PHPUnit cannot currently run in this environment because the project requires PHP 8.3 while the local machine has PHP 8.2.
