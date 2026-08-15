# Notification Catalog

This document lists the persistent notifications currently implemented for the
Chess Puzzle Challenge platform. Notifications use Laravel's database and mail
channels and are queued after the transaction that caused them commits.

## Delivery Rules

| Audience | In-app location | Email | Preferences |
| :--- | :--- | :--- | :--- |
| Player | Player navigation bell and `/notifications` | Yes, when enabled | `/profile` |
| Admin / Editor / Fulfillment | Filament notification bell in `/admin` | Yes, when enabled | Staff-only notifications are not shown in the player bell or player preferences |

Player notifications are stored in the `notifications` table. The player bell
only reads player notification types, so staff operational notifications remain
inside the Filament admin panel.

## Player Notifications

### 1. Challenge Enrollment Activated

- **Class:** `App\Notifications\EnrollmentActivatedNotification`
- **Preference key:** `enrollment_activated`
- **Trigger:** An enrollment is created with status `active` after payment confirmation.
- **Recipient:** The enrolled player.
- **In-app title:** `Your challenge is ready`
- **In-app content:** `You are enrolled in {challenge_name}. Start solving whenever you are ready.`
- **In-app destination:** The challenge player at `/play/{enrollment}`.
- **Email template:** `challenge_enrollment`
- **Email subject:** `You're enrolled in {challenge_name}`
- **Email title:** `Good luck, {user_name}`
- **Email content:** `Hi {user_name}, You have successfully enrolled in {challenge_name}. Click the button below to start solving puzzles.`
- **Email button:** `Start Challenge`
- **Email destination:** The challenge player.

### 2. Challenge Completed

- **Class:** `App\Notifications\ChallengeCompletedNotification`
- **Preference key:** `challenge_completed`
- **Trigger:** An enrollment changes to status `completed` after the final puzzle is finalized.
- **Recipient:** The player who completed the challenge.
- **In-app title:** `Challenge complete!`
- **In-app content:** `Congratulations, you completed {challenge_name}. Your sticker is unlocked. You can now request your medal and leave a review.`
- **In-app destination:** The enrollment page at `/enrollments/{enrollment}`.
- **Email template:** `challenge_completion`
- **Email subject:** `Congratulations on completing {challenge_name}!`
- **Email title:** `Challenge complete!`
- **Email content:** `Hi {user_name}, Congratulations on completing {challenge_name}. Your medal fulfilment status: {fulfillment_status}.`
- **Email button:** `Track Shipment`
- **Email destination:** The enrollment page.
- **Note:** Sticker unlocked, medal request, and review availability are intentionally merged into this single notification to avoid notification noise.

### 3. Medal Shipped

- **Class:** `App\Notifications\MedalShippedNotification`
- **Preference key:** `medal_shipped`
- **Trigger:** Fulfillment changes to status `shipped`, either through courier processing or an admin status update.
- **Recipient:** The player who owns the fulfillment.
- **In-app title:** `Your medal has shipped`
- **In-app content:** `Your {challenge_name} medal is on its way.`
- **In-app destination:** The tracking URL when available; otherwise the enrollment page.
- **Email template:** `medal_shipped`
- **Email subject:** `Your medal has shipped: {challenge_name}`
- **Email title:** `Your medal is on the way`
- **Email content:** `Hi {user_name}, Your medal for {challenge_name} has shipped. Follow the tracking link below for updates.`
- **Email button:** `Track Medal`
- **Email destination:** The tracking URL when available.

### 4. Medal Delivered

- **Class:** `App\Notifications\MedalDeliveredNotification`
- **Preference key:** `medal_delivered`
- **Trigger:** Fulfillment changes to status `delivered` from the fulfillment admin workflow.
- **Recipient:** The player who owns the fulfillment.
- **In-app title:** `Your medal was delivered`
- **In-app content:** `Your {challenge_name} medal has been marked as delivered.`
- **In-app destination:** The enrollment page at `/enrollments/{enrollment}`.
- **Email template:** `medal_delivered`
- **Email subject:** `Your medal was delivered`
- **Email title:** `Medal delivered`
- **Email content:** `Hi {user_name}, Your medal for {challenge_name} has been marked as delivered. We hope you enjoy it!`
- **Email button:** `View Challenge`
- **Email destination:** The enrollment page.

### 5. Review Published or Featured

- **Class:** `App\Notifications\ReviewFeaturedNotification`
- **Preference key:** `review_featured`
- **Trigger:** An admin changes a review's `is_public` or `is_featured` flag to `true`.
- **Recipient:** The player who submitted the review.
- **In-app title:** `Your review is now public`
- **In-app content:** `Your review for {challenge_name} is now featured on Chess Puzzle Challenge.`
- **In-app destination:** The public home page.
- **Email template:** `review_featured`
- **Email subject:** `Your review is now public`
- **Email title:** `Thank you for your review`
- **Email content:** `Hi {user_name}, Your review for {challenge_name} is now public on {platform_name}.`
- **Email button:** `View Reviews`
- **Email destination:** The public home page.

## Admin Notifications

These notifications are delivered to the Filament admin notification bell.
They are not displayed in the player navigation bell or in player notification
preferences.

### 6. Medal Request Ready to Ship

- **Class:** `App\Notifications\MedalRequestReadyNotification`
- **Preference key:** `medal_request_ready`
- **Trigger:** A fulfillment is created or changes to `ready_to_ship` after a player submits a medal address.
- **Recipients:** Users with the `super_admin` or `fulfillment` role.
- **In-app title:** `Medal request ready to ship`
- **In-app content:** `A player requested the {challenge_name} medal. Open the fulfillment queue to dispatch it.`
- **Admin destination:** `/admin/operations/fulfillment-queue`.
- **Email template:** `medal_request_ready`
- **Email subject:** `Medal request ready to ship`
- **Email title:** `Fulfillment action required`
- **Email content:** `A medal request for {challenge_name} is ready to ship.`
- **Email button:** `Open Fulfillment Queue`
- **Email destination:** `/admin/operations/fulfillment-queue`.

### 7. Review Awaiting Moderation

- **Class:** `App\Notifications\ReviewAwaitingModerationNotification`
- **Preference key:** `review_awaiting_moderation`
- **Trigger:** A player review changes to status `submitted`.
- **Recipients:** Users with the `super_admin` or `editor` role.
- **In-app title:** `Review awaiting moderation`
- **In-app content:** `A new review for {challenge_name} is ready for moderation.`
- **Admin destination:** `/admin/reviews`.
- **Email template:** `review_awaiting_moderation`
- **Email subject:** `Review awaiting moderation`
- **Email title:** `New review to moderate`
- **Email content:** `A new review for {challenge_name} is awaiting moderation.`
- **Email button:** `Review Submission`
- **Email destination:** `/admin/reviews`.

## Notification Management

### Players

Players manage the five player notification types from **Profile →
Notification preferences** at `/profile`.

Each type has two independent switches:

- **In account:** Controls the database notification, notification bell, and notification center.
- **Email:** Controls the queued email notification.

All player notification channels are enabled by default. Existing users with no
saved preferences also receive notifications by default. Marking a notification
as read only changes its in-app `read_at` value and does not affect email
preferences.

### Admin Users

Admin, editor, and fulfillment users manage operational notifications through
the Filament notification bell. Operational notification types are intentionally
not exposed in the player profile preferences and do not appear in the player
notification center.

### Email Templates

Administrators can edit notification email content in **Admin → Settings →
Email / SMTP**. The editable template keys are:

- `challenge_enrollment`
- `challenge_completion`
- `medal_shipped`
- `medal_delivered`
- `review_featured`
- `medal_request_ready`
- `review_awaiting_moderation`

Template placeholders are replaced when the notification is delivered. Common
placeholders include `{{user_name}}`, `{{challenge_name}}`,
`{{platform_name}}`, `{{tracking_url}}`, and `{{fulfillment_status}}`.

## Existing Framework Notifications

These are separate from the lifecycle notification catalog above and are
provided by Laravel authentication:

- **Email verification:** Sent after registration through Laravel's `Registered` event.
- **Password reset:** Sent when a password reset is requested.
- **Password reset confirmation:** Sent when a password is successfully reset.

Filament success and error toasts are also transient UI messages. They are not
stored in the `notifications` table and are not included in the persistent
notification bell.
