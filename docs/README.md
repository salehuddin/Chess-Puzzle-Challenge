# Chess Puzzle Challenge — Documentation

This folder contains documentation for every feature and page in the application.
Each feature has its own markdown file with a consistent template structure so the team can fill in details as the project evolves.

## Documentation Structure

### Public / Marketing
Publicly accessible pages that do not require authentication.

- [Home / Landing Page](./features/public/home.md)
- [Challenges Listing](./features/public/challenges.md)
- [Challenge Detail Page](./features/public/challenge-detail.md)
- [Challenge Enrollment](./features/public/challenge-enrollment.md)
- [Bundle Enrollment](./features/public/bundle-enrollment.md)
- [Mockups (Single Challenge)](./features/public/mockups.md)

### Authentication
User registration, login, and password management.

- [Login](./features/auth/login.md)
- [Register](./features/auth/register.md)
- [Forgot Password](./features/auth/forgot-password.md)
- [Reset Password](./features/auth/reset-password.md)
- [Verify Email](./features/auth/verify-email.md)
- [Confirm Password](./features/auth/confirm-password.md)

### User Dashboard
Features available to authenticated users after logging in.

- [User Dashboard](./features/user-dashboard/dashboard.md)
- [Puzzle Player](./features/user-dashboard/puzzle-player.md)
- [Medal Request](./features/user-dashboard/medal-request.md)
- [Hall of Fame](./features/user-dashboard/hall-of-fame.md)
- [Order Tracking](./features/user-dashboard/order-tracking.md)
- [Checkout](./features/user-dashboard/checkout.md)
- [Profile](./features/user-dashboard/profile.md)

### Admin Panel (Filament)
Administrative features for managing the platform.

- [Admin Dashboard](./features/admin/dashboard.md)
- [Challenge Management](./features/admin/challenges.md)
- [Bundle Management](./features/admin/bundles.md)
- [Puzzle Management](./features/admin/puzzles.md)
- [Enrollment Management](./features/admin/enrollments.md)
- [Order Management](./features/admin/orders.md)
- [Fulfillment Management](./features/admin/fulfillments.md)
- [User Management](./features/admin/users.md)
- [Fulfillment Queue](./features/admin/fulfillment-queue.md)
- [Medal Inventory](./features/admin/medal-inventory.md)
- [Settings](./features/admin/settings.md)

## How to Use This Documentation

1. Open the feature file you want to document.
2. Replace the placeholder sections with accurate details.
3. Keep related files, permissions, and user flows up to date as the code changes.
4. Add screenshots or diagrams under each feature when helpful.

## Template Sections

Each feature file follows this structure:

- **Overview** — What the feature does.
- **Purpose** — Why the feature exists.
- **User Flow** — Step-by-step interaction.
- **Key Components** — Important classes, components, or modules.
- **Related Files** — Links to relevant code files.
- **Permissions / Access Control** — Who can use the feature.
- **Notes** — Edge cases, limitations, or future improvements.
