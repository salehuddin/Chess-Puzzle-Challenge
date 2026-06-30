# Puzzle Player

The puzzle player is the interactive chess board where players solve puzzles to progress through a challenge. It's the core gameplay experience.

## How It Works

1. The player opens a challenge they're enrolled in.
2. The chess board loads the first unsolved puzzle.
3. The player sees the board position and must find the correct move sequence.
4. After solving (or attempting) a puzzle, the next one loads automatically.
5. Progress is saved after each puzzle — players can leave and come back.

## Game Flow

### Solving a Puzzle

- The board shows the opponent's last move (the "trigger" move).
- The player must find the correct response. The solution moves are pre-defined.
- If the player makes a wrong move, the board resets and they can try again.
- Once the correct move is played, the puzzle is marked as solved and the next one loads.

### The Last Puzzle

- When the player reaches the final puzzle, a special "Complete Challenge" button appears.
- Solving the last puzzle triggers the completion flow:
  - The enrollment status changes to `completed`.
  - A sticker is awarded to the player.
  - A fulfillment record is created for medal shipping.

### Progress Tracking

- A progress bar shows solved / total puzzles (e.g. "45 / 100").
- Solved puzzle IDs are tracked — if the player refreshes, they resume where they left off.
- The puzzle sequence is fixed per challenge (set by the admin on the Puzzles tab).

## Completion Flow

When all puzzles are solved:

1. Enrollment status → `completed`, `completed_at` timestamp recorded.
2. A `Sticker` record is created for the player.
3. A `Fulfillment` record is created with status `pending`.
4. The player sees a congratulations screen with their sticker and medal shipment info.

## Access Control

- Only the enrolled user can access the puzzle player (enforced by `user_id` check).
- The enrollment must be `active` or `completed` — pending or other statuses are blocked.

## Related Files

- `app/Livewire/PuzzlePlayer.php` — The Livewire component with game logic
- `resources/views/livewire/puzzle-player.blade.php` — The player template
- `routes/web.php` — Route: `/play/{enrollment}`
