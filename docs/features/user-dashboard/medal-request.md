# Medal Request

The medal request page allows players to submit or update their shipping address for medal fulfillment after completing a challenge.

## How It Works

1. A player completes all puzzles in a challenge.
2. A fulfillment record is created with status `pending`.
3. The player is prompted to submit their shipping address (if not already on file).
4. Once the address is confirmed, the fulfillment moves to `ready_to_ship`.
5. The admin can then dispatch the medal via the Fulfillment Queue.

## Address Collection

- If the player already has an address in their profile, it's pre-filled.
- The player can update their address before confirming.
- The address is snapshot into the fulfillment record at the time of submission.

## Related Files

- `app/Livewire/MedalRequest.php` — The Livewire component
- `resources/views/livewire/medal-request.blade.php` — The template
- `routes/web.php` — Route: `/medal-request/{enrollment}`
