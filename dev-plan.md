# Development Plan: Chess Puzzle Challenge Platform

This document serves as the high-level roadmap and real-time status tracker for the platform's development, derived directly from the core PRD. 

---

## 🛠 Active Technical Stack
*To prevent legacy scaffolding collisions, all development must strictly adhere to the following framework constraints:*

| Framework | Target Version |
| :--- | :--- |
| **PHP** | 8.3+ |
| **Laravel Framework** | v13 |
| **Filament Admin** | v5 |
| **Livewire** | v4 |
| **AlpineJS** | v3 |
| **TailwindCSS** | v4 |

---

## 🟢 Status: Completed Milestones (Backend & Core)

The foundational architecture, administrative controls, and core data engines have been securely finalized.

1. **Database & Architecture Scaffolding**
    - [x] Bootstrapped Laravel 13, Livewire 4, Alpine 3, and Tailwind v4.
    - [x] Defined Migrations & Models for `Users`, `Puzzles`, `Challenges`, `Subscriptions`, and `Bundles`.
    - [x] Configured authentications with Laravel Breeze.

2. **Admin Infrastructure (Filament v5)**
    - [x] Built the `ChallengeResource` with Rich Text Editors and Pricing matrices.
    - [x] Designed the `PuzzlesRelationManager` allowing admins to visually filter and dynamically inject Lichess puzzles directly into their curated challenges.
    - [x] Configured `SubscriptionsResource` for tracking payment statuses, user address snapshots, and shipment tracking URLs.

3. **Chess Gameplay Engine Integration**
    - [x] Wrapped `Chessground` UI and `Chess.js` mathematical logic inside a highly responsive AlpineJS/Livewire component (`PuzzlePlayer`).
    - [x] Mounted a secure, read-only Interactive Puzzle Preview directly into the Filament Admin editing screens to visually verify database accuracy.

4. **Big Data Ingestion Pipeline**
    - [x] Built a highly-optimized, memory-safe Laravel Queue Job (`ImportLichessPuzzlesJob`).
    - [x] Created a specialized Filament Custom Page (`ImportPuzzles`) providing UI filters (Min Rating, Max Rating, Limits, Themes) to automatically harvest the uncompressed 3GB Lichess CSV stream without crashing the HTTP server.

---

## 🟡 Status: In Progress (Next Steps)

With the database easily populateable and the core mechanics wired, we must now build out the specific UX journeys where users interact with the platform.

### Milestone 1: The User Mechanics (Dashboard & Progression)
*Targeting the player's direct gameplay loop from purchase to completion.*

- [ ] **The "Hall of Fame" Dashboard:** Build the `/dashboard` frontend page where logged-in Users can view their owned Challenges, track their progress, and display their unlocked "Digital Stickers".
- [ ] **Challenge Loading Loop:** Mount the `PuzzlePlayer` logic onto a dedicated gameplay route (e.g. `/play/{challenge_id}`) so players can iteratively run through the 100 attached puzzles in sequence without refreshing.
- [ ] **"Proof of Work" Completion Trigger:** Wire the frontend AlpineJS logic so that beating the final puzzle securely signals a backend Livewire endpoint. This endpoint must stamp their `<Subscription>` table record as `Completed`, unlock their Digital Sticker, and queue them into the Admin shipping list.

### Milestone 2: Commerce (Stripe PPP)
*Targeting the monetization pipeline.*

- [ ] **Purchasing Power Parity (PPP):** Implement basic GeoIP detection on the frontend to display customized pricing (MYR for Malaysian IPs, USD for global users).
- [ ] **Checkout Flow:** Configure Stripe Elements (or Stripe Checkout Sessions) for secure FPX (Malaysia) and Credit Card payments.
- [ ] **Fulfillment Webhook:** Build a secure API Webhook listener to receive Stripe fulfillment signals. This hook automatically creates the `Paid` `Subscription` database row mapping the paying User to the `Challenge` they bought.

### Milestone 3: Logistics & Polish
*Targeting edge cases, styling, and final preparations.*

- [ ] **Address Snapshotting:** Enforce that players enter their explicit shipping address prior to checkout so that the Admin can correctly fulfill the physical medal post-gameplay.
- [ ] **Aesthetic Enhancements:** Finalize the landing page and public styling using the DaisyUI/Tailwind design system.
- [ ] **Logistics Tracking Integration:** Ensure the Admin can update `Subscriptions` to `Shipped` with a valid Courier Tracking URL that populates the user's Dashboard visually.

---

## 🔵 Admin Dashboard Execution Plan (Tracked Step-by-Step)

This is our active implementation checklist for improving the admin dashboard and related fulfillment flows. We will update this section as each item is completed.

- [x] **Step 1: Scope & Success Criteria**
    Define and lock dashboard KPIs, logistics transition rules, and whether bundle management is included in this pass.

- [x] **Step 2: Custom Global Admin Dashboard**
    Build a custom Filament dashboard experience with business KPI widgets and fulfillment visibility.

- [x] **Step 3: Central Fulfillment Queue View**
    Create a dedicated admin page/table for orders needing logistics action with practical filters.

- [x] **Step 4: Enforce Status Transition Rules**
    Prevent invalid status updates (e.g., shipped without required tracking data) and align behavior with business workflow.

- [x] **Step 5: Normalize Subscription Transition Logic**
    Consolidate status/timestamp mutation logic so all admin entry points behave consistently.

- [ ] **Step 6: Bundle Admin Resource**
    Add Filament bundle management (list/create/edit) and challenge assignment support.

- [ ] **Step 7: Documentation Sync**
    Reconcile roadmap/checklists with actual implementation status to remove stale TODOs.

- [ ] **Step 8: Automated Tests for Admin-Critical Paths**
    Add feature tests for transition rules, fulfillment filters/actions, and dashboard widget load behavior.

- [ ] **Step 9: Final QA Pass**
    Validate end-to-end admin workflows with realistic data and check for regressions in user-facing tracking screens.

### Step 1 Lock (May 31, 2026)

#### Dashboard KPIs (Global Admin Dashboard)
- Paid Subscriptions: statuses `paid`, `in_progress`, `completed`, `shipped`.
- Active In Progress: status `in_progress`.
- Completed (Awaiting Shipment): status `completed`.
- Shipped: status `shipped`.
- Pending Payment: status `pending`.
- Projected Revenue (USD): sum of challenge USD price for paid subscriptions.
- 7-Day New Paid Subscriptions: trend value for operational monitoring.

#### Logistics Transition Rules (Canonical)
- `pending -> paid`: allowed via payment/override flow.
- `paid -> in_progress`: allowed when gameplay starts (already implemented).
- `in_progress -> completed`: allowed when challenge completion proof is valid (already implemented).
- `completed -> shipped`: allowed only when `courier` and at least one of `tracking_number` or `tracking_url` is present.
- `shipped`: must stamp `shipped_at` automatically when first entering shipped.
- `completed`: must stamp `completed_at` automatically when first entering completed.
- Reverse transitions from `shipped` require explicit admin override action (not inline default edit).

#### Scope Decision For This Pass
- Bundle management is included in this pass.
- Target deliverable: a `Bundle` Filament resource with list/create/edit and challenge assignment ordering.

#### Definition of Done (Admin Dashboard Workstream)
- Custom dashboard page shows all locked KPIs.
- Fulfillment queue page exists with filters for completed-not-shipped and shipped-missing-tracking.
- Transition guardrails enforced consistently across all admin edit entry points.
- Feature tests cover transition validation and key admin actions.

### Step 2 Delivery Notes (May 31, 2026)

- Added custom admin dashboard page class at `app/Filament/Pages/Dashboard.php`.
- Rewired panel provider to use the custom dashboard page.
- Added `AdminKpiOverview` widget with locked KPI metrics, including 7-day paid trend.
- Added `FulfillmentSnapshot` widget for operational logistics visibility.

### Step 3 Delivery Notes (May 31, 2026)

- Added top-level Filament page `FulfillmentQueue` at `app/Filament/Pages/FulfillmentQueue.php`.
- Added page view at `resources/views/filament/pages/fulfillment-queue.blade.php`.
- Queue is focused on fulfillment statuses (`completed`, `shipped`) and includes practical filters:
    - Completed Not Shipped
    - Shipped Missing Tracking
- Added direct record action to open the related `Subscription` edit page for logistics updates.

### Step 4 Delivery Notes (June 1, 2026)

- Added canonical subscription status constants and transition helper methods in `Subscription` model.
- Enforced transition guardrails on `EditSubscription` and `CreateSubscription` pages.
- Enforced inline transition guardrails in `ChallengeMedalStatus` table edits.
- Added logistics requirements before shipping (`courier` + at least one tracking field).
- Auto-stamped lifecycle timestamps on first transition:
    - `completed_at` when status first becomes `completed`
    - `shipped_at` when status first becomes `shipped`
- Made lifecycle timestamps read-only in the subscription form UI to reflect auto-managed behavior.

### Step 5 Delivery Notes (June 1, 2026)

- Centralized lifecycle mutation/validation in `Subscription` model:
    - `applyLifecycleUpdate()`
    - `createWithLifecycle()`
    - `normalizeLifecycleData()`
- Refactored `EditSubscription` and `CreateSubscription` to use the centralized model workflow.
- Refactored inline updates in `ChallengeMedalStatus` to call the centralized lifecycle workflow.
- Refactored gameplay status transitions in `PuzzlePlayer` to use the same centralized workflow.

---

## 🧭 Domain Model Refactor Plan (Orders + Enrollments)

This workstream aligns the business language and schema with real product behavior:
- `Order` = what the user paid for.
- `Enrollment` = what challenge access the user has.
- `Fulfillment` = shipping lifecycle for physical rewards.

### Target Model (V2)

- `orders`
    - `id`, `user_id`, `status` (`pending`, `paid`, `failed`, `refunded`)
    - `currency`, `subtotal_amount`, `discount_amount`, `total_amount`
    - `payment_provider`, `payment_intent_id`, `paid_at`, `metadata`, timestamps

- `order_items`
    - `id`, `order_id`, `item_type` (`challenge`, `bundle`), `item_id`
    - `name_snapshot`, `sku_snapshot`, `unit_price`, `quantity`, `line_total`, `meta`, timestamps

- `enrollments`
    - `id`, `user_id`, `challenge_id`, `order_item_id` nullable
    - `status` (`active`, `completed`), `activated_at`, `completed_at`, timestamps

- `fulfillments`
    - `id`, `enrollment_id`, `status` (`pending`, `ready_to_ship`, `shipped`, `delivered`)
    - `address_snapshot`, `courier`, `tracking_number`, `tracking_url`, `shipped_at`, timestamps

### Transition Strategy (No Big-Bang Rewrite)

- [x] **Phase 1: Introduce V2 Tables (Additive Migrations)**
    Create new tables with indexes and foreign keys. Keep `subscriptions` intact.

- [x] **Phase 2: Dual-Write From Existing Flows**
    Keep current `subscriptions` writes while also writing equivalent `orders`, `order_items`, `enrollments`, and `fulfillments`.

- [x] **Phase 3: Backfill Historical Data**
    Build idempotent backfill command/job to map each existing subscription into V2 records.

- [x] **Phase 4: Switch Read Paths Incrementally**
    Migrate UI in this order: Admin Fulfillment Queue, Dashboard, Order Tracking, Gameplay Access checks.

- [ ] **Phase 5: Deprecate Subscription-Centric Reads**
    Freeze old read usage behind feature flags and remove after parity verification.

### Naming Decision

- Recommended user-facing term: **Enrollment**.
- Internal fallback alias during migration: `ChallengeAccess` (optional), but do not expose both in UI copy.

### Acceptance Criteria For V2 Cutover

- Bundle purchase creates one order with one bundle item and multiple challenge enrollments.
- Single challenge purchase creates one order, one item, and one enrollment.
- Fulfillment dashboard reads from `fulfillments` and remains operationally equivalent or better.
- Existing users keep access and shipping history after backfill with no data loss.

### V2 Phase 1 Delivery Notes (June 1, 2026)

- Added additive migrations:
    - `create_orders_table`
    - `create_order_items_table`
    - `create_enrollments_table`
    - `create_fulfillments_table`
- Added baseline models:
    - `Order`
    - `OrderItem`
    - `Enrollment`
    - `Fulfillment`
- Existing `subscriptions` flow remains untouched and backward compatible.

### V2 Phase 2 Delivery Notes (June 1, 2026)

- Added `SubscriptionObserver` to automatically sync each saved subscription into V2 records.
- Registered observer in `AppServiceProvider`.
- Added `source_subscription_id` link on `orders` for deterministic idempotent upserts.
- Dual-write mapping behavior:
    - `subscriptions` -> `orders` (status/payment snapshot)
    - `subscriptions` -> `order_items` (challenge line item snapshot)
    - paid+ lifecycle statuses -> `enrollments`
    - completion/shipping lifecycle statuses -> `fulfillments`
- Current subscription-based UX remains live while V2 data is continuously populated.

### V2 Phase 3 Delivery Notes (June 1, 2026)

- Added reusable `SubscriptionV2SyncService` so backfill and observer use the same sync logic.
- Refactored `SubscriptionObserver` to delegate to `SubscriptionV2SyncService`.
- Added explicit relation `Subscription::v2Order()` for missing-order targeting.
- Added `app:backfill-subscription-v2` Artisan command with options:
    - `--dry-run`
    - `--only-missing-order`
    - `--subscription-id=`
    - `--chunk=`
    - `--limit=`
- Registered the command in `bootstrap/app.php` via `withCommands()`.
- Verified in runtime:
    - Dry run reported 1 subscription candidate.
    - Actual backfill processed 1 subscription with 0 failures.

### V2 Phase 4 Progress Notes (June 1, 2026)

- Migrated **admin fulfillment queue page** to read from `fulfillments` + `enrollments` instead of `subscriptions`.
- Migrated **admin KPI/fulfillment widgets** to prefer V2 tables (`orders`, `enrollments`, `fulfillments`) with legacy fallback.
- Migrated **user dashboard** to read V2 enrollment/order/fulfillment projections with legacy fallback.
- Added new admin resources/pages for V2 operations:
    - `OrderResource`
    - `EnrollmentResource`
    - `FulfillmentResource`
- Migrated **user order tracking page** to use V2 order/enrollment/fulfillment data with legacy fallback.
- Migrated **gameplay access checks** to enforce V2 enrollment entitlement when available.

### V2 Phase 5 Progress Notes (June 1, 2026)

- Added feature flag config `config/features.php` with `FEATURE_V2_READS` toggle.
- Gated V2 read-path switching in:
    - User dashboard (`App\\Livewire\\Dashboard`)
    - User order tracking (`App\\Livewire\\OrderTracking`)
    - Gameplay entitlement checks (`App\\Livewire\\PuzzlePlayer`)
    - Admin KPI and fulfillment widgets
- Added `FEATURE_V2_READS=true` to `.env.example` for controlled rollback during parity validation.

---

## 🟢 V2-Only Refactor (June 10, 2026) — Complete

The V1-to-V2 dual-write transition has been finalized. All V1 infrastructure has been removed and the system runs entirely on the V2 data model with zero legacy fallbacks.

### What Was Scrapped

```
Deleted Files:
  app/Observers/SubscriptionObserver.php
  app/Services/SubscriptionV2SyncService.php
  app/Console/Commands/BackfillSubscriptionV2.php
  app/Models/Subscription.php
  app/Models/UserPuzzleProgress.php
  database/factories/SubscriptionFactory.php
  app/Filament/Resources/Subscriptions/ (entire directory)
  config/features.php
  tests/Feature/PuzzlePlayerEnhancementsTest.php
  tests/Feature/MilestoneOneFlowTest.php

Dropped Tables:
  subscriptions
  user_puzzle_progress
  orders.source_subscription_id (FK + column from model)

Removed Config:
  FEATURE_V2_READS (from .env.example)

Removed Code References:
  AppServiceProvider — SubscriptionObserver registration
  bootstrap/app.php — BackfillSubscriptionV2 command registration
  All `config('features.v2_reads')` / `canUseV2()` gates across Livewire & Filament
```

### What Was Created

```
New Table: puzzle_progress
  user_id + challenge_id + puzzle_id  (unique composite)
  solved_at (nullable)
  Foreign keys → users, challenges, puzzles

New Model: PuzzleProgress
  Table: puzzle_progress
  Relationships: user(), challenge(), puzzle()
```

### What Was Rewritten

| File | Change |
|---|---|
| `app/Livewire/PuzzlePlayer.php` | Accepts `Enrollment`, uses `PuzzleProgress` + `user_id/challenge_id`, no Subscription lifecycle |
| `app/Livewire/Dashboard.php` | Single `buildCards()` path from Enrollments, PuzzleProgress by `user_id+challenge_id` |
| `app/Livewire/OrderTracking.php` | Accepts `Enrollment`, reads fulfillment directly |
| `app/Http/Controllers/ChallengeEnrollmentController.php` | Creates Order + OrderItem → Enrollment, no Subscription |
| `app/Filament/Widgets/AdminKpiOverview.php` | V2-only: Orders, Enrollments, Fulfillments |
| `app/Filament/Widgets/FulfillmentSnapshot.php` | V2-only: Fulfillments |
| `app/Filament/Resources/Challenges/Pages/ChallengeAnalytics.php` | `enrollments()` instead of `subscriptions()` |
| `app/Filament/Resources/Challenges/Pages/ChallengeMedalStatus.php` | Enrollment + Fulfillment instead of Subscription |
| `app/Filament/Resources/Challenges/Widgets/ChallengeQuickGlance.php` | Enrollment stats instead of Subscription |
| `app/Filament/Resources/Fulfillments/Schemas/FulfillmentForm.php` | User address instead of sourceSubscription |
| `routes/web.php` | `/play/{enrollment}`, `/orders/{enrollment}` |
| `resources/views/livewire/dashboard.blade.php` | `$pendingCards`, `$activeCards`, `$completedCards`, enrollment-based routing |
| `resources/views/livewire/order-tracking.blade.php` | `enrollment_id` for play link |
| `app/Models/Challenge.php` | Removed `subscriptions()`, added `puzzleProgress()` |
| `app/Models/User.php` | Removed `subscriptions()`, added `puzzleProgress()` |
| `app/Models/Order.php` | Removed `source_subscription_id` fillable, removed `sourceSubscription()` |

### Final Data Model

```
Challenge ────< Puzzle (via challenge_puzzle.pivot, sequence)
    │
    ├── Bundle (via bundle_challenge.pivot, sort_order)
    │     │ SKU: BUND-00001
    │     │ price, name, slug, is_active
    │
    │ SKU: CHAL-00001
    │ price, slug, content (Editor.js), artwork, videos, rules
    │
    └── Enrollment (user_id + challenge_id, unique)
            │ status: active → completed
            │ order_item_id (traceable FK)
            │
            ├── PuzzleProgress (user_id + challenge_id + puzzle_id, unique)
            │     solved_at
            │
            └── Fulfillment (enrollment_id, unique)
                  status: pending → ready_to_ship → shipped → delivered
                  address_snapshot, courier, tracking_number, tracking_url

Order (user_id)
  status: pending → paid → failed/refunded
  currency, subtotal, discount, total
  └── OrderItem (polymorphic item_type + item_id)
        name_snapshot, sku_snapshot (canonical SKU from product)
        unit_price, quantity, line_total
        └── Enrollment (via order_item_id)

Sticker (user_id + challenge_id, unique)
  unlocked_at — earned on completion
```

### User Flow (V2-Only)

```
Browse Challenges → Challenge Detail → Enroll
    │
    │  (creates Order + OrderItem → Enrollment)
    │
    ├── Admin: Order status = paid → Enrollment active → redirect /play/{enrollment}
    │        │
    │        └── Solve puzzles one by one → PuzzleProgress tracked
    │              │
    │              └── All solved → Enrollment: completed → Sticker unlocked
    │                    └── Fulfillment: ready_to_ship
    │
    └── User: Order status = pending → redirect /checkout/{order}
             ├── Sandbox mode ON: Pay with Sandbox → Order paid → play
             └── Sandbox mode OFF / Stripe: await real payment (Milestone 2)
```

### Routes

| Method | URI | Handler | Name |
|---|---|---|---|
| GET | `/challenges` | ChallengeIndex | challenges.index |
| GET | `/challenges/{challenge:slug}` | ChallengeShow | challenges.show |
| GET | `/challenges/{challenge:slug}/enroll` | ChallengeEnrollmentController | challenges.enroll |
| GET | `/dashboard` | Dashboard | dashboard |
| GET | `/checkout/{order}` | CheckoutController@show | checkout.show |
| POST | `/checkout/{order}/pay` | CheckoutController@pay | checkout.pay |
| GET | `/play/{enrollment}` | PuzzlePlayer | play |
| GET | `/orders/{enrollment}` | OrderTracking | orders.track |
| GET | `/hall-of-fame` | HallOfFame | hall-of-fame |

### Admin (Filament) Resources

| Resource | Group | Purpose |
|---|---|---|
| Challenges | — | CRUD, Content, Puzzles, Players, Medal Status, Analytics |
| Puzzles | — | CRUD, Bulk Import (Lichess) |
| Users | — | CRUD |
| Orders | Commerce | CRUD, multi-item via Repeater |
| Enrollments | Commerce | CRUD, activation/completion tracking |
| Bundles | Commerce | CRUD, challenge assignment (BelongsToManyMultiSelect) |
| Fulfillments | Operations | CRUD, Queue page for logistics |
| FulfillmentQueue | Operations | Custom page: ready_to_ship + shipped filters |

### Remaining TODOs (from original Milestones)

| Milestone | Task | Status |
|---|---|---|
| M1 | Hall of Fame Dashboard | Done |
| M1 | PuzzlePlayer gameplay loop | Done |
| M1 | Completion trigger → sticker + fulfillment | Done |
| M2 | Stripe payment integration | Not started |
| M2 | GeoIP pricing (MYR/USD) | Not started |
| M2 | Payment webhook → auto-enrollment | Not started |
| — | Sandbox / dummy payment mode | Done |
| M3 | Address snapshot at checkout | Done (at enrollment) |
| M3 | Public styling / landing page | Partial |
| M3 | Courier tracking URL in user dashboard | Done |
| Admin S6 | Bundle Admin Resource | Done |
| Admin S8 | Automated tests | Not started |
| Admin S9 | Final QA pass | Not started |
