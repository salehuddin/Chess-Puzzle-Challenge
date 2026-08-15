# Challenge Lifecycle — End-to-End Workflow

This document maps every activity in the full life of a chess challenge — from creating the
challenge and importing puzzles, through player purchase and play, to medal fulfillment and
finally to the player's review. For each step it states **who** performs it and **on which
page**.

> Roles referenced below (see `app/Models/User.php`): **Admin**, **Editor**, **Fulfillment**
> (staff), and **Player** (registered user). "Admin panel" = Filament at `/admin`.

---

## Phase 0 — Setup & configuration (one-time, by Admin)

| # | Activity | Who | Page |
| :-: | :--- | :--- | :--- |
| 0.1 | Register / confirm an admin account | Admin | `/register` → `/admin/login` |
| 0.2 | Configure payment mode (sandbox on/off) — `SANDBOX_PAYMENT_MODE` | Admin | `.env` / Coolify env |
| 0.3 | Configure courier provider (none / EasyParcel / DHL), API keys, HQ address, parcel defaults | Admin | Admin → **Settings** (`app/Filament/Pages/Settings.php`) |

---

## Phase 1 — Create the challenge (Admin/Editor)

| # | Activity | Who | Page |
| :-: | :--- | :--- | :--- |
| 1.1 | Create challenge: name, slug, SKU, description | Admin / Editor | Admin → **Challenges → New Challenge** (`ChallengeResource` create) |
| 1.2 | Set progression policy: navigation mode (strict sequential / free order / strict-skip tokens / strict-autosolve), skip-token count, autosolve threshold | Admin / Editor | Create Challenge form; **Challenges → [challenge] → Details** |
| 1.3 | Complete details: status (draft/published/archived), **Active** toggle, USD/MYR price, meta/description, poster + medal artwork + medal gallery + sticker artwork | Admin / Editor | **Challenges → [challenge] → Details** |
| 1.4 | Set medal shipping dimensions (weight/length/width/height) | Admin / Editor | **Challenges → [challenge] → Details** |
| 1.5 | Set initial medal inventory: stock on hand, reorder threshold, reorder quantity | Admin / Editor | **Challenges → [challenge] → Details** |
| 1.6 | Write content: EditorJS blocks, image gallery, videos, T&Cs, FAQ | Admin / Editor | **Challenges → [challenge] → Content** |
| 1.7 | Publish + activate the challenge (so it appears on the public site) | Admin / Editor | **Challenges → [challenge] → Details** |

---

## Phase 2 — Import puzzles & build the puzzle list

| # | Activity | Who | Page |
| :-: | :--- | :--- | :--- |
| 2.1 | Bulk-import Lichess puzzle CSV into the shared puzzle pool | Admin / Editor (CLI) | `php artisan puzzles:import` (`app/Console/Commands/ImportLichessPuzzles.php`) — optional job `ImportLichessPuzzlesJob` |
| 2.2 | Or upload a Lichess CSV **and attach** matching puzzles directly to this challenge (auto-dedup + sequence) | Admin / Editor | **Challenges → [challenge] → Puzzles → Import CSV** |
| 2.3 | Or create puzzles manually one-by-one (Lichess ID, FEN, moves, rating, themes…) | Admin / Editor (CLI) | Admin → **Puzzles → New Puzzle** |
| 2.4 | Attach puzzle(s) from the shared pool to the challenge (filter by rating/theme/plays; bulk attach) | Admin / Editor | **Challenges → [challenge] → Attach Puzzles** |
| 2.5 | Quick-attach all puzzles uploaded in the last hour | Admin / Editor | **Challenges → [challenge] → Puzzles → Quick Attach Recent** |
| 2.6 | Manage order: drag to reorder, randomize, sort by rating ASC/DESC | Admin / Editor | **Challenges → [challenge] → Puzzles** |
| 2.7 | Preview a puzzle on a board before attaching | Admin / Editor | **Attach Puzzles / Puzzles** (side preview panel) |

---

## Phase 3 — Player discovers & purchases (public site)

| # | Activity | Who | Page |
| :-: | :--- | :--- | :--- |
| 3.1 | Register / log in | Player | `/register`, `/login` |
| 3.2 | Browse the challenge store | Player | `/challenges` (`ChallengeIndex`), `/` (home) |
| 3.3 | View a single challenge (content, gallery, videos, medal, pricing, FAQ) | Player | `/challenges/{slug}` (`ChallengeShow`) |
| 3.4 | Start enrollment / go to checkout | Player | `/challenges/{slug}/enroll` (`ChallengeEnrollmentController`) or `/bundles/{slug}/enroll` |
| 3.5 | (Bundle) Purchase a bundle covering multiple challenges | Player | `/bundles/{slug}/enroll` (`BundleEnrollmentController`) |
| 3.6 | Complete sandbox payment → order becomes **paid** | Player | `/checkout/{order}` (`CheckoutController`); pay at `/checkout/{order}/pay` |

**Under the hood (payment confirmation):** `CommerceHierarchyService::syncFromOrder()` creates an
**Enrollment** (status `active`, `activated_at` set) for every challenge in the order (including
all bundle members). The enrollment is now ready to play.

---

## Phase 4 — Player plays the challenge

| # | Activity | Who | Page |
| :-: | :--- | :--- | :--- |
| 4.1 | Open player for the enrollment | Player | `/play/{enrollment}` (`PuzzlePlayer`) |
| 4.2 | Solve puzzles sequentially (or free-order from the grid) | Player | `/play/{enrollment}` |
| 4.3 | Use hints / wrong-move attempts (counted server-side) | Player | `/play/{enrollment}` |
| 4.4 | In strict-skip mode: spend a skip token to pass a puzzle and revisit it later | Player | `/play/{enrollment}` |
| 4.5 | In strict-autosolve mode: reveal solution after the attempt threshold (marks "solved with help") | Player | `/play/{enrollment}` |
| 4.6 | View solve history, puzzle grid, and progress | Player | `/play/{enrollment}` (More info panel) |
| 4.7 | Complete the final puzzle → enrollment flips to **completed** | Player (auto) | `/play/{enrollment}` (auto-finalize) |

**On completion (PuzzlePlayer::finalizeIfEligible):** a **Sticker** is unlocked, a **Fulfillment**
record is created (status `pending`), and a pending **Review** row is created.

---

## Phase 5 — Medal request & fulfillment (staff)

| # | Activity | Who | Page |
| :-: | :--- | :--- | :--- |
| 5.1 | Player supplies shipping address for the medal | Player | `/medal-request/{enrollment}` (`MedalRequest`) |
| 5.2 | Address saved → fulfillment moves **pending → ready_to_ship** (reserves stock) | Player (auto) | `/medal-request/{enrollment}` |
| 5.3 | Review / work the fulfillment queue (ready-to-ship & shipped) | Admin / Fulfillment / Editor | Admin → **Operations → Fulfillment Queue** (`FulfillmentQueue`) |
| 5.4 | Dispatch one or many shipments → queues a courier job | Admin / Fulfillment / Editor | **Fulfillment Queue → Dispatch Shipment / Dispatch Selected** |
| 5.5 | Courier job creates tracking (EasyParcel/DHL) → fulfillment **shipped**; medal stock **decremented** | System (queue; `ProcessCourierShipmentJob`) | background (`app/Jobs/ProcessCourierShipmentJob.php`) |
| 5.6 | Manually update courier / tracking # / URL if no courier provider | Admin / Fulfillment / Editor | **Fulfillment Queue → Open Fulfillment**, or **Fulfillments → edit** |
| 5.7 | Mark shipment **delivered** | Admin / Fulfillment / Editor | **Fulfillments → edit**, or **Challenges → [challenge] → Medal Status** (inline) |
| 5.8 | Manage medal inventory: restock, adjust, view movement history | Admin / Fulfillment | Admin → **Operations → Medal Inventory** (`MedalInventory`) |
| 5.9 | Track overall medal fulfillment per challenge, inline status/courier/tracking | Admin / Fulfillment / Editor | **Challenges → [challenge] → Medal Status** (`ChallengeMedalStatus`) |

**Player visibility of the medal:** the player sees the derived status (preparing / shipped /
delivered) and tracking link on their **Dashboard** (`/dashboard`) and **Enrolled Challenge**
page (`/enrollments/{enrollment}`, `EnrolledChallenge`).

---

## Phase 6 — Player review (final step)

| # | Activity | Who | Page |
| :-: | :--- | :--- | :--- |
| 6.1 | Player submits review (puzzle rating + platform rating 1–5, optional headline/body) — fulfills the pending Review | Player | `/play/{enrollment}` (after completion) **or** `/enrollments/{enrollment}` (inline form) |
| 6.2 | Player can complete their profile collection (view earned stickers) | Player | `/dashboard` (Collection tab), `/hall-of-fame` (`HallOfFame`) |
| 6.3 | Admin reviews/moderation: set status, mark **public** (testimonial) and/or **featured** | Admin / Editor | Admin → **Reviews** (`ReviewResource`); edit at `/admin/reviews/{id}/edit` |
| 6.4 | Approved public/featured reviews surface on the landing page testimonials | Public | `/` (home page) |

---

## Lifecycle at a glance (state transitions)

```
[Challenge: draft] -> publish+active -> [Challenge: published]
     |
     | Puzzles imported + attached (>= 1)
     v
[Order: pending] --sandbox pay--> [Order: paid]
     |
     v
[Enrollment: active]  --solve all puzzles-->  [Enrollment: completed]
     |                                              | (sticker + fulfillment pending + review pending)
     |                                              v
     |                                     [Medal request: satisfied]
     |                                              |
     |        Fulfillment: pending -> ready_to_ship -> shipped -> delivered
     |                                              | (stock decremented on ship)
     |                                              v
     |                                     [Review: pending]
     |                                              | player submits
     |                                              v
     +---------------------------------------> [Review: submitted] -> (admin) public/featured
```

---

## Hooks, triggers & notifications (by phase)

This section lists, for each activity/phase, the **hooks** (model events, DB-transaction
auto-behaviour), **triggers** (events, queued jobs, browser events), **in-app notifications**
(Filament toasts), **emails**, and **audit log** entries the system performs.

> **Implementation note:** Lifecycle notifications are now implemented with Laravel
> Notification classes. They use the database and mail channels, are queued after the
> originating transaction commits, and use editable templates from Admin → **Settings →
> Email / SMTP**. Player notifications appear in the player bell and `/notifications`;
> staff notifications appear only in the Filament admin notification bell.

| # | Phase / activity | Hook / trigger | In-app notification / toast | Event / job | Email | Audit hook |
| :-: | :--- | :--- | :--- | :--- | :--- | :--- |
| 0.2 | Payment mode toggle | Settings row saved | Filament toast on save | — | — | — |
| 0.3 | Courier / HQ / parcel config | Settings row saved | Filament toast on save | — | — | — |
| 1.1 | Challenge created | Model `created` hook auto-fills SKU (`CHAL-0000N`) if blank (`Challenge::booted`) | — | — | — | Spatie Activitylog (User `LogsActivity` trait) logs `User` model changes only |
| 1.2–1.4 | Challenge details/status/active | — | Filament toast on save | — | — | — |
| 1.5 | Set medal stock on hand / threshold | Stock editable in form; initialized balance | — | — | — | — |
| 1.6 | Content / media saved | — | Filament toast on save | — | — | — |
| 2.1 | CLI bulk import | Streams CSV, `Puzzle::insertOrIgnore` (dedup on `lichess_id`) | CLI progress table | `ImportLichessPuzzlesJob` (optional queued variant) | — | — |
| 2.2 | Admin CSV import + attach | `CsvPuzzleService::importRowsForChallenge` (pool dedup + pivot attach + renumber sequence) | Filament "Import complete" toast (imported/attached/skipped counts) | — | — | — |
| 2.3 | Manual puzzle create | Puzzle saved | Filament toast | — | — | — |
| 2.4 | Attach puzzles from pool | `challenge_puzzle` pivot `attach()` + `normalizeSequence()` | Filament "N puzzle(s) attached" toast | — | — | — |
| 2.5 | Quick attach recent (1h) | Query `created_at >= now-1h` → attach | Filament toast | — | — | — |
| 2.6 | Reorder / randomize / sort sequence | DB transaction rewrites pivot `sequence` | — | — | — | — |
| 3.1 | Register | `event(new Registered($user))` → Laravel sends **verification email** | — | `Registered` event | ✅ **Verification email** (built-in) | — |
| 3.2–3.3 | Browse / view challenge | — | — | — | — | — |
| 3.4 | Enroll (challenge or bundle) | DB transaction creates `Order` (status `pending`) + `OrderItem` via `syncFromSelections` | Flash status message (`redirect()->with('status')`) | — | — | — |
| 3.6 | Sandbox payment → `paid` | DB transaction sets `paid`, `paid_at`; then `syncFromOrder()` **creates Enrollments** (`active` + `activated_at`) | Flash status message; player receives **Challenge enrollment activated** notification | `EnrollmentObserver::created` → `EnrollmentActivatedNotification` | ✅ `challenge_enrollment` template | — |
| 4.1–4.2 | Load player / solve puzzle | Proof-token (nonce, encrypted, 24h) verification; `PuzzleProgress` upsert sets `solved_at` | Browser toast via Livewire event `puzzle-solved` | Livewire event `puzzle-loaded`, `puzzle-solved` | — | Puzzle solves appear in admin **User Activity Timeline** |
| 4.3 | Hint / wrong attempt | `PuzzleProgress` increments `hints_used` / `attempts` | — | Livewire event `attempt-recorded` | — | — |
| 4.4 | Skip puzzle (strict-skip) | `PuzzleProgress` sets `skipped_at` (requires skip token) | `skip-denied` browser toast when out of tokens | Livewire event `skip-denied` | — | — |
| 4.5 | Autosolve (strict-autosolve) | `PuzzleProgress` sets `solved_at` + `solved_with_help=true` | Toast via `puzzle-solved` | Livewire event `puzzle-solved` | — | — |
| 4.7 | **Enrollment completed** | `PuzzlePlayer::finalizeIfEligible()` in a DB transaction: enrollment `completed` + `completed_at`, **Sticker unlocked**, **Fulfillment created** (`pending`), **Review created** (`pending`) | Player receives one merged **Challenge completed** notification covering sticker, medal request, and review availability | `EnrollmentObserver::updated` → `ChallengeCompletedNotification` | ✅ `challenge_completion` template | Shows in User Activity Timeline (`Completed …`, `Medal/sticker unlocked …`) |
| 5.1–5.2 | Medal request / address | DB transaction sets fulfillment `ready_to_ship` + address snapshot (optionally saves to profile) | Browser toast via Livewire event `medal-requested`; staff receive **Medal request ready to ship** in Filament | Livewire event `medal-requested`; `FulfillmentObserver::created/updated` → `MedalRequestReadyNotification` | ✅ `medal_request_ready` template for `super_admin` / `fulfillment` | — |
| 5.3–5.4 | Dispatch shipment(s) | Bulk/record action queues courier job | Filament "Shipment queued" toast | `ProcessCourierShipmentJob::dispatch()` (queued) | — | — |
| 5.5 | **Courier dispatch (async)** | Job calls EasyParcel/DHL; on success sets fulfillment `shipped` + `shipped_at` + tracking, and **decrements medal stock** via `MedalInventoryService::decrementForShipment` | Player receives **Medal shipped** notification with tracking destination | `ProcessCourierShipmentJob` runs in queue; `FulfillmentObserver::updated` → `MedalShippedNotification`; failure logged and rethrown | ✅ `medal_shipped` template | Appears in User Activity Timeline (`Medal shipped …`); stock movement recorded |
| 5.6–5.7 | Manual tracking update / deliver | Inline edit sets courier/#/URL; `status` → `delivered` sets `delivered_at` | Filament toast on errors; player receives **Medal delivered** notification | `FulfillmentObserver::updated` → `MedalDeliveredNotification` | ✅ `medal_delivered` template | Appears in User Activity Timeline (`Medal delivered …`) |
| 5.8 | Restock / adjust inventory | `MedalInventoryService` records a `MedalStockMovement` (initial/restock/adjustment/return) in a DB transaction | Filament "Stock restocked"/"Stock adjusted" toast; movements modal | — | — | Movement is logged to `medal_stock_movements` with actor |
| 5.9 | Inline medal status per challenge | Inline dropdown updates fulfillment status/delivered timestamp | Filament toast when no fulfillment | — | — | — |
| 6.1 | **Review submitted** (player) | `Review` flips `pending`→`submitted` + `submitted_at`; validates ratings | Browser toast via Livewire event `review-submitted`; `super_admin` / `editor` receive **Review awaiting moderation** in Filament | Livewire event `review-submitted`; `ReviewObserver::updated` → `ReviewAwaitingModerationNotification` | ✅ `review_awaiting_moderation` template | — |
| 6.3 | Admin review moderation | Admin marks `is_public` / `is_featured` | Filament toast on save; player receives **Review published or featured** notification | `ReviewObserver::updated` → `ReviewFeaturedNotification` | ✅ `review_featured` template | — |
| 6.4 | Public testimonials | Landing page queries `Review::public()` / `featured()` | — | — | — | — |

---

## Hook / trigger catalog (reference)

### Laravel application events
| Event | Where fired | Effect |
| :--- | :--- | :--- |
| `Illuminate\Auth\Events\Registered` | `RegisteredUserController` | Sends the **email verification** email (built-in) |
| `Illuminate\Auth\Events\Verified` | `VerifyEmailController` | Marks the account verified |
| `Illuminate\Auth\Events\Lockout` | `LoginRequest` | Throttle lockout (may be emailed per config) |
| `Illuminate\Auth\Events\PasswordReset` | `NewPasswordController`, `SetUserPasswordAction` | Sends/confirms password reset |

### Notification observers

Lifecycle notifications are detected centrally by model observers. This covers
changes made by Livewire, Filament forms/tables, services, and queued jobs.

| Observer | Transition | Notification | Recipient |
| :--- | :--- | :--- | :--- |
| `EnrollmentObserver` | Enrollment created as `active` | `EnrollmentActivatedNotification` | Enrolled player |
| `EnrollmentObserver` | Enrollment status becomes `completed` | `ChallengeCompletedNotification` | Completing player |
| `FulfillmentObserver` | Fulfillment created or becomes `ready_to_ship` | `MedalRequestReadyNotification` | `super_admin`, `fulfillment` |
| `FulfillmentObserver` | Fulfillment becomes `shipped` | `MedalShippedNotification` | Player |
| `FulfillmentObserver` | Fulfillment becomes `delivered` | `MedalDeliveredNotification` | Player |
| `ReviewObserver` | Review status becomes `submitted` | `ReviewAwaitingModerationNotification` | `super_admin`, `editor` |
| `ReviewObserver` | Review becomes public or featured | `ReviewFeaturedNotification` | Reviewing player |

All lifecycle notifications use `ShouldQueue` and `afterCommit()` so a rolled
back transaction does not create a notification from an uncommitted state.

### Model `booted` hooks
| Model | Hook | Effect |
| :--- | :--- | :--- |
| `Challenge` | `static::created` | If SKU blank, auto-generates `CHAL-0000N` (`Challenge.php:56`) |
| `Bundle` | `static::created` | Auto-generates bundle SKU (`Bundle.php:31`) |

### Queued jobs (triggers)
| Job | Triggered by | Effect |
| :--- | :--- | :--- |
| `ImportLichessPuzzlesJob` | artisan/optional | Streams Lichess CSV into the `puzzles` pool (dedup) |
| `ProcessCourierShipmentJob` | Fulfillment Queue dispatch action/bulk | Creates tracking, sets fulfillment `shipped`, decrements medal stock |

### Livewire browser events (`$this->dispatch(...)`) — UI-side, no backend fan-out
| Event | Emitted from | Signals |
| :--- | :--- | :--- |
| `puzzle-loaded` | `PuzzlePlayer` | Board re-render on puzzle change |
| `puzzle-solved` | `PuzzlePlayer` | Solved toast + progress count |
| `skip-denied` | `PuzzlePlayer` | Out-of-tokens toast |
| `attempt-recorded` | `PuzzlePlayer` | Wrong-attempt counter update |
| `medal-requested` | `MedalRequest` | Address/request saved toast |
| `review-submitted` | `PuzzlePlayer`, `EnrolledChallenge` | Review saved toast |
| `avatar-updated` | `UpdateAvatar` | Profile avatar refreshed |

### Filament in-app notifications (`Notification::make()`)
Filament toast notifications are fired in admin pages/actions on success/failure — e.g.
`FulfillmentQueue` ("Shipment queued"), `MedalInventory` ("Stock restocked"/"Stock adjusted"),
`ChallengePuzzles`/`AttachPuzzles` ("Import complete"/"N puzzle(s) attached"),
`Settings` (save confirmations), `Challenges`/admin resource actions. These are transient
admin-only UI toasts; they are not persisted and not emailed.

### Persistent Laravel notifications

The persistent notification catalog is documented in
[`docs/notifications.md`](notifications.md). The database channel stores rows in
`notifications`, while the mail channel uses the configured Laravel mail transport
and editable Settings email templates.

Player notifications:

- `EnrollmentActivatedNotification` — enrollment is ready to play.
- `ChallengeCompletedNotification` — completion, sticker, medal request, and review availability are merged into one message.
- `MedalShippedNotification` — medal shipment and tracking.
- `MedalDeliveredNotification` — medal delivery confirmation.
- `ReviewFeaturedNotification` — review became public or featured.

Admin-only notifications:

- `MedalRequestReadyNotification` — fulfillment work is ready to ship.
- `ReviewAwaitingModerationNotification` — a player review needs moderation.

The Filament panel has database notifications enabled. The player navigation bell
and `/notifications` page intentionally filter out the two admin-only notification
types.

### Audit log
- **Spatie Activitylog** is installed and the `User` model uses `LogsActivity`
  (`app/Models/User.php:26`) with an `activities()` morphMany — so **User record changes**
  (create/update/delete/restore) are logged and viewable under Admin → **Users → [user] →
  Activity Log**, including an "attribute changes" diff.
- The **User Activity Timeline** widget (`UserActivityTimeline.php`) is *not* a hook — it
  *derives* a chronological feed on page load by querying the player's Orders, Enrollments,
  PuzzleProgress solves, Stickers, and Fulfillments (order placed, payment received, enrolled,
  started, solved puzzle, completed, medal/sticker unlocked, medal shipped, medal delivered).
- **MedalStockMovement** rows are the audit trail for physical medal inventory (initial,
  restock, shipment, adjustment, return) — see `MedalInventoryService::recordMovement()`.

### Gaps (deliberate — no implementation present)
- No heartbeat/webhook to external services (e.g. shipment status webhook from EasyParcel/DHL
  updating `delivered` automatically — currently manual).
- No order receipt notification is currently dispatched by the checkout flow. The existing
  `order_receipt` email template remains available for a future order confirmation notification.
- No scheduled background tasks in `routes/console.php` or `Kernel` (no auto-restock alerts,
  no stale-order sweeps, no low-stock email).

---

## Key source files

| Concern | Files |
| :--- | :--- |
| Challenge create/edit/content/inventory | `app/Filament/Resources/Challenges/**` |
| Puzzle import | `app/Console/Commands/ImportLichessPuzzles.php`, `app/Jobs/ImportLichessPuzzlesJob.php`, `app/Services/CsvPuzzleService.php` |
| Purchase / enrollment creation | `app/Http/Controllers/*EnrollmentController.php`, `app/Services/CommerceHierarchyService.php` |
| Sandbox payment | `app/Http/Controllers/CheckoutController.php`, `app/Services/Settings.php` |
| Playing + finalize + review submit | `app/Livewire/PuzzlePlayer.php`, `app/Livewire/EnrolledChallenge.php` |
| Medal request | `app/Livewire/MedalRequest.php` |
| Fulfillment / courier / inventory | `app/Filament/Pages/FulfillmentQueue.php`, `app/Filament/Pages/MedalInventory.php`, `app/Jobs/ProcessCourierShipmentJob.php`, `app/Services/MedalInventoryService.php` |
| Review review/moderation | `app/Filament/Resources/Reviews/**` |
| User-facing dashboard | `app/Livewire/Dashboard.php`, `app/Livewire/ChallengeIndex.php`, `app/Livewire/ChallengeShow.php`, `app/Livewire/HallOfFame.php` |
