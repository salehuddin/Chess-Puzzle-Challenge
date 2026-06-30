This Product Requirements Document (PRD) outlines the "Chess Puzzle Challenge" platform—a bridge between digital chess mastery and physical rewards.

---

# PRD: Chess Medal Challenge Platform

## 1. Executive Summary
The platform allows users to participate in themed chess puzzle "Series" (e.g., *Ultimate Winter 2026*). Upon completing a set of 100 puzzles, users receive a **digital sticker** immediately and a **custom-designed physical medal** via mail. The service targets the global chess community with a focus on fair pricing via Purchasing Power Parity (PPP) for the Malaysian and Global markets.

---

## 2. User Roles & Personas
* **The Challenger (User):** Chess enthusiasts who want to test their skills and earn tangible trophies.
* **The Administrator (Admin):** Manages puzzle series, sets pricing, handles shipping, and monitors logistics.

---

## 3. Functional Requirements

### 3.1. Puzzle Engine & Gameplay
* **Puzzle Sourcing:** Use Lichess Open Database (FEN format). Puzzles are filtered by rating, theme (e.g., Fork, Pin), and length (e.g., $\le$ 5 moves).
* **Chess UI:** Interactive board using **Chessground** for the interface and **Chess.js** for move validation.
* **Gameplay Mechanics:**
    * **Undo Logic:** Users can "Undo" moves freely without penalty.
    * **State Persistence:** Progress (current puzzle index, FEN state) is saved in `localStorage` to allow resuming after a refresh or tab closure.
* **Completion:** The browser only notifies the server once the entire series is finished. A "Proof of Work" token (session-based) is sent to prevent simple API spoofing.

### 3.2. Series & Challenge Management
* **Series Creator:** Admin can generate a series by specifying difficulty ranges, themes, and quantity.
* **Rules Engine:** Flexible rules per series (e.g., Sequential order vs. Random order, Time limits).
* **Digital Stickers:** Each series has a unique digital sticker/badge that is automatically unlocked in the user's "Hall of Fame" upon completion.

### 3.3. Payments & PPP
* **Automatic PPP:** Detects user location (GeoIP). Users in Malaysia see prices in **MYR**; all others see **USD**.
* **Stripe Integration:** Supports **FPX** (popular in Malaysia) and **Credit Cards**.
* **Bundling:** Admin can create "Bundles" of multiple series with flexible pricing. 
    * *Note:* Bundles grant access to all included series, but medals are triggered for shipment individually as each series is completed.

### 3.4. Address & Fulfillment
* **Address Snapshotting:** Users have a default address in their profile. At the time of registration/payment for a series, the address is "snapshotted" into that specific challenge record to ensure delivery accuracy even if the user moves later.
* **Logistics Dashboard:** Admin marks challenges as "Shipped" and enters a tracking number.
* **Direct Tracking:** The platform provides a direct link to the courier’s tracking page (PosLaju, FedEx, DHL, etc.) for the user.

---

## 4. Technical Stack
* **Framework:** Laravel 13 (PHP 8.4+).
* **Admin:** Filament v5.
* **Frontend:** TALL Stack (Tailwind CSS v4, Alpine.js 3, Laravel Livewire 4) + DaisyUI 5.
* **Database:** MySQL 8 (production), SQLite (local).
* **Chess Libraries:** `Chessground` (UI), `Chess.js` (Move logic).
* **Payments:** Stripe (with FPX and Webhook support).
* **Data Ingestion:** Custom Laravel Artisan command to parse and filter Lichess `.csv.zst` files into a local curated database.
* **Deployment:** Coolify on VPS (Docker, nginx + PHP-FPM via supervisord, Traefik reverse proxy, Let's Encrypt SSL).

---

## 5. Data Schema Overview (High-Level)

| Entity | Key Data Points |
| :--- | :--- |
| **User** | Profile, Default Address, Hall of Fame (Stickers). |
| **Puzzle** | FEN, Solution, Rating, Themes, Popularity. |
| **Challenge** | Name, Medal Artwork, Sticker Artwork, Rule JSON, USD/MYR Price. |
| **Subscription** | User ID, Challenge ID, Status (Paid, Completed, Shipped), **Address Snapshot**, Tracking URL. |
| **Bundle** | Bundle Name, List of Challenges, Custom Price (USD/MYR). |

---

## 6. Success Metrics
* **Completion Rate:** Percentage of users who finish a series after paying.
* **Global Reach:** Ratio of MYR vs. USD transactions.
* **Customer Satisfaction:** Successful delivery of physical medals without address errors.

---

## 7. Roadmap & Future Considerations
* **V2:** Google Maps API integration for address autocomplete.
* **V2:** Automated email notifications for shipping updates.
* **V3:** Animated/Premium digital stickers.
* **V3:** Social sharing features for the "Hall of Fame."

---

## 8. Deployment Status

| Item | Status |
| :--- | :--- |
| **Production URL** | https://chesspuzzlechallenge.com |
| **Hosting** | VPS (Ubuntu 26.04, 4 vCPU, 8 GB RAM) via Coolify |
| **Build** | Docker (multi-stage: `php:8.4-fpm` + `node:22` + nginx + supervisord) |
| **Database** | MySQL 8 (Coolify-managed container) |
| **Reverse Proxy** | Traefik (Coolify built-in, auto Let's Encrypt SSL) |
| **CI/CD** | Push to `main` triggers Coolify auto-deploy |
| **Deployment Script** | `deploy-coolify.ps1` (PowerShell, automates Coolify API) |
| **Admin Access** | Filament panel at `/admin` (requires admin user creation via tinker) |