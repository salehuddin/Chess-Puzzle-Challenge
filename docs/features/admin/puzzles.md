# Puzzle Management

Puzzles are the individual chess positions that make up challenges. Each puzzle comes from the Lichess database and contains a board position (FEN), the solution moves, a difficulty rating, and thematic tags. Players solve these puzzles one by one to progress through a challenge.

## Puzzle List

The main Puzzles page shows every puzzle in the database with these columns:

- **Lichess ID** — The unique identifier from Lichess. Clicking a row opens a chess board preview showing the position and solution.
- **Rating** — The difficulty rating (ELO). Higher numbers = harder puzzles.
- **Themes** — Tags like `mateIn2`, `fork`, `pin`, `sacrifice`, etc. Shown as badges.
- **Popularity** — Lichess popularity score (-100 to +100). Higher is better.
- **Nb Plays** — How many times the puzzle has been played on Lichess.

### Filters

- **Rating Range** — Filter by minimum and/or maximum rating (e.g. 800–1500 for beginners).
- **Theme** — Multi-select filter for puzzle themes. Searchable dropdown with all available themes.
- **Min Plays** — Only show puzzles with at least this many plays. Useful for filtering out obscure puzzles.

### Bulk Actions

Select multiple puzzles using the checkboxes, then:

- **Delete** — Remove selected puzzles from the database.
- **Attach to Challenge** — Pick a challenge from a dropdown and attach all selected puzzles in one go. Duplicates are skipped. Sequence numbers are auto-assigned.

## Importing Puzzles from Lichess

The **Import** page lets you bulk-import puzzles from a Lichess CSV file. This is the primary way to populate the puzzle database.

### Setup

1. Download the Lichess puzzle database CSV from [database.lichess.org](https://database.lichess.org/#puzzles).
2. Upload the CSV file to `storage/app/` on your server (via SFTP, FTP, or direct file copy).
3. Go to **Puzzles → Import** in the admin panel.

### Import Settings

- **CSV Filename** — The filename inside `storage/app/`. Default: `lichess_db_puzzle.csv`.
- **Minimum ELO Rating** — Skip puzzles below this rating.
- **Maximum ELO Rating** — Skip puzzles above this rating.
- **Minimum Hit Popularity Rating** — Default 80. Puzzles below this score are usually visually glitchy or low quality.
- **Enforce Required Themes** — Comma-separated theme names (e.g. `mateIn2,fork`). Only import puzzles matching these themes. Case sensitive.
- **Maximum Insertion Limit** — Default 100,000. Lichess has ~4 million puzzles, so this cap keeps the database manageable and the admin panel fast.

### Running the Import

Click **Dispatch Import** to start. A background job (`ImportLichessPuzzlesJob`) processes the CSV in chunks. You can leave the page — the job runs via the Laravel queue worker.

**Important:** Make sure `php artisan queue:work` is running on your server, otherwise the import job won't process.

## CSV Explorer

A separate page for browsing puzzles directly from the raw CSV file without importing them. Useful for previewing what's in the file before committing to an import.

## Editing a Puzzle

Each puzzle has these fields:

- **Lichess ID** — Unique identifier. Must be unique across the database.
- **FEN** — The chess position in Forsyth-Edwards Notation. This is what the chess board renders.
- **Moves** — The solution moves as an array. First move is the opponent's move that triggers the puzzle.
- **Rating / Rating Deviation** — Difficulty and confidence interval.
- **Popularity / Nb Plays** — Quality metrics from Lichess.
- **Themes** — Thematic tags.
- **Game URL** — Link to the original Lichess game.

When editing, a live chess board preview appears at the top showing the puzzle position.

## Related Files

- `app/Filament/Resources/Puzzles/PuzzleResource.php` — Resource definition
- `app/Filament/Resources/Puzzles/Tables/PuzzlesTable.php` — List table with filters and bulk actions
- `app/Filament/Resources/Puzzles/Schemas/PuzzleForm.php` — Create/edit form
- `app/Filament/Resources/Puzzles/Pages/ImportPuzzles.php` — Bulk import from Lichess CSV
- `app/Filament/Resources/Puzzles/Pages/CsvExplorer.php` — CSV preview tool
- `app/Filament/Resources/Puzzles/Support/PuzzleThemes.php` — Available theme options
- `app/Jobs/ImportLichessPuzzlesJob.php` — Background import worker
