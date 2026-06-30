# Deployment Guide — Chess Puzzle Challenge

This guide deploys the app to a **VPS** using **Coolify** (open-source self-hosting platform). Steps are written so they can be executed by a human or an autonomous agent (each numbered step is a discrete, verifiable action).

> **Why Coolify?** It's a self-hosted Heroku/Netlify alternative that auto-detects Laravel, provisions databases, manages SSL, runs queue workers, and auto-deploys from Git. No per-app subscription, unlike Laravel Forge/Ploi.

> **Production Status (June 30, 2026):** Successfully deployed to https://chesspuzzlechallenge.com using the Dockerfile build pack (not Nixpacks) with `php:8.4-fpm` + nginx + supervisord. See `deploy-coolify.ps1` for the API automation script.

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Prerequisites](#2-prerequisites)
3. [Provision the Hostinger VPS](#3-provision-the-hostinger-vps)
4. [Install Coolify](#4-install-coolify)
5. [Connect the GitHub Repository](#5-connect-the-github-repository)
6. [Provision the Database](#6-provision-the-database)
7. [Configure the Application](#7-configure-the-application)
8. [Set Environment Variables](#8-set-environment-variables)
9. [Post-Deployment Commands](#9-post-deployment-commands)
10. [Upload the Lichess CSV (Manual)](#10-upload-the-lichess-csv-manual)
11. [Domain & SSL](#11-domain--ssl)
12. [CI/CD with GitHub Actions](#12-cicd-with-github-actions)
13. [Alternatives](#13-alternatives)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Architecture Overview

```
GitHub Repo ──push──> Coolify (on VPS) ──build──> Docker Container
                                               │
                                               ├── Nginx (HTTP :80, reverse proxy to PHP-FPM)
                                               ├── PHP-FPM 8.4 (Laravel application)
                                               ├── MySQL 8 (Coolify-managed container)
                                               └── Supervisord (process manager: nginx + php-fpm)
```

Coolify uses **Docker** with a custom multi-stage `Dockerfile` to build a production image with PHP 8.4, nginx, and the Laravel app. It then runs the container and routes traffic via **Traefik** (built-in reverse proxy with Let's Encrypt SSL).

> **Note:** Nixpacks auto-detection was initially attempted but failed due to PHP version mismatches (composer.lock requires PHP 8.4+). A custom Dockerfile is used instead.

---

## 2. Prerequisites

| Requirement   | Details                                                                                                |
|:------------- |:------------------------------------------------------------------------------------------------------ |
| Hostinger VPS | KVM 2 plan minimum (2 vCPU, 8GB RAM, 50GB NVMe). 4GB RAM works but is tight for MySQL + Coolify + app. |
| OS            | Ubuntu 22.04 LTS (recommended) or 24.04                                                                |
| Domain        | Optional for staging; required for production SSL (e.g., `staging.yourdomain.com`)                     |
| GitHub repo   | https://github.com/salehuddin/Chess-Puzzle-Challenge                                                   |
| SSH access    | Root or sudo user on the VPS                                                                           |
| Local SSH key | For agent SSH access if running CI/CD autonomously                                                     |

### Required secrets (gather before starting)

- `APP_KEY` — generate with `php artisan key:generate` locally; copy the base64 key.
- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` — from Stripe dashboard (use test keys for staging).
- `DB_PASSWORD` — a strong password you choose for the MySQL database.

---

## 3. Provision the Hostinger VPS

### 3.1 Create the VPS

1. Log into Hostinger → **VPS** → **Create new VPS**.
2. Choose **KVM 2** (or higher), **Ubuntu 22.04 LTS**, a datacenter close to your users.
3. Set a root password (save it securely).
4. Wait for provisioning (5–10 min). Note the public IP.

### 3.2 Initial server hardening (run as root via SSH)

```bash
# SSH into the server
ssh root@YOUR_VPS_IP

# Update the system
apt update && apt upgrade -y

# Install essential packages
apt install -y curl git ufw fail2ban

# Configure firewall (Coolify needs these ports)
ufw allow 22/tcp       # SSH
ufw allow 80/tcp       # HTTP
ufw allow 443/tcp      # HTTPS
ufw allow 8000/tcp     # Coolify dashboard (temporary, can close later)
ufw --force enable

# Create a non-root deploy user (optional but recommended)
adduser deploy
usermod -aG sudo deploy
mkdir -p /home/deploy/.ssh
cp /root/.ssh/authorized_keys /home/deploy/.ssh/
chown -R deploy:deploy /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
```

### 3.3 Verify

```bash
ufw status verbose
# Expected: 22, 80, 443, 8000 ALLOW
```

---

## 4. Install Coolify

### 4.1 Run the official installer

```bash
# As root or sudo user:
curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash
```

This script:

- Installs Docker and Docker Compose.
- Pulls the Coolify management container.
- Starts Coolify on port `8000`.

Installation takes 5–10 minutes.

### 4.2 Access the Coolify dashboard

1. Open `http://YOUR_VPS_IP:8000` in a browser.
2. Create the admin account (email + password).
3. Log in.

### 4.3 Verify

```bash
docker ps | grep coolify
# Expected: several coolify-* containers running
```

---

## 5. Connect the GitHub Repository

### 5.1 Add a GitHub deployment key (in Coolify)

1. In Coolify dashboard → **Settings** → **Sources** → **GitHub**.
2. Click **Install GitHub App** (recommended) or add a **Deploy Key** / **Personal Access Token**.
3. Authorize Coolify for the `salehuddin/Chess-Puzzle-Challenge` repository.

### 5.2 Create a new project and application

1. Coolify dashboard → **Projects** → **New Project** → name it `chess-puzzle-challenge`.
2. Inside the project → **New Resource** → **Public Repository** (or Private if the repo is private).
3. Select `salehuddin/Chess-Puzzle-Challenge`.
4. Choose **Production** environment (or **Staging** for a preview).

### 5.3 Set the build pack

1. In the application settings → **Build Pack** → select **Nixpacks**.
2. Coolify will auto-detect Laravel. Verify it shows:
   - PHP version: 8.3
   - Composer detected
   - Node.js detected (for Vite build)

### 5.4 Configure build commands (if auto-detection misses anything)

In **Build Commands** section:

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Build frontend assets
npm ci
npm run build

# (Nixpacks usually runs these automatically for Laravel)
```

In **Start Commands** (Nixpacks Laravel preset handles this, but verify):

```bash
# Nixpacks runs php-fpm automatically.
# Queue worker and scheduler are configured in step 9.
```

### 5.5 Verify

Click **Deploy** (or **Save & Deploy**). Watch the build logs. The first build takes 5–10 minutes. A successful build shows a green "Deployed" status with a URL.

---

## 6. Provision the Database

### 6.1 Create a MySQL database in Coolify

1. In the same Coolify project → **New Resource** → **Database**.
2. Choose **MySQL 8**.
3. Set:
   - Database name: `chess_puzzle`
   - Root password: (auto-generated or set your own)
   - Database user: `chess_app`
   - Database password: `<your strong password>`
4. Click **Deploy**.

### 6.2 Connect the database to the app

1. Go back to the **Chess Puzzle Challenge** application in Coolify.
2. **Settings** → **Databases** → link the MySQL database you just created.
3. Coolify automatically injects `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` environment variables into the app container.

### 6.3 Verify

```bash
# Inside the app container (via Coolify terminal or docker exec)
php artisan db:show
# Expected: shows MySQL connection and database name
```

---

## 7. Configure the Application

### 7.1 Run migrations automatically on deploy

In Coolify → Application → **Settings** → **Post-deployment command**:

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> These run after each successful deploy inside the app container.

### 7.2 Configure a queue worker

In Coolify → Application → **Settings** → **Simple Dockerfile** or **Workers** section:

Add a **simple worker** with:

```bash
php artisan queue:work --tries=3 --timeout=90 --max-time=3600
```

This runs as a sidecar container alongside the web container, sharing the same image.

### 7.3 Configure the scheduler

Laravel's scheduler needs a cron entry. In Coolify → Application → **Settings** → **Cron Jobs**:

```bash
* * * * * php artisan schedule:run >> /dev/null 2>&1
```

Or, if using Laravel 11+'s built-in scheduler daemon (recommended):

Add another **simple worker**:

```bash
php artisan schedule:work
```

### 7.4 Verify

After deploying, check the Coolify **Logs** tab for the worker container — you should see it waiting for jobs. Trigger a CSV import in the admin panel to confirm the queue picks it up.

---

## 8. Set Environment Variables

In Coolify → Application → **Environment Variables**, add the following:

### Required

```env
APP_NAME="Chess Puzzle Challenge"
APP_ENV=production
APP_KEY=<base64 key from php artisan key:generate>
APP_DEBUG=false
APP_URL=https://staging.yourdomain.com

DB_CONNECTION=mysql
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD are auto-injected by Coolify

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# Stripe (use test keys for staging)
STRIPE_KEY=<pk_test_...>
STRIPE_SECRET=<sk_test_...>
STRIPE_WEBHOOK_SECRET=<whsec_...>
```

### Optional (for this app)

```env
# Enable sandbox payment mode for staging
SANDBOX_PAYMENT_MODE=true

# GeoIP (for PPP pricing)
# If using MaxMind, set the license key
MAXMIND_LICENSE_KEY=
```

### 8.1 Verify

```bash
php artisan config:cache
php artisan tinker --execute="echo config('app.env');"
# Expected: production
```

---

## 9. Post-Deployment Commands

After the first successful deploy, run these once (via Coolify's **Terminal** tab on the app container, or as a one-time post-deployment command):

```bash
# Run migrations
php artisan migrate --force

# Create the storage symlink
php artisan storage:link

# Seed roles (required for admin access)
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=SettingsSeeder

# Create a super admin user (interactive — run in terminal)
php artisan tinker
# >>> $u = User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>bcrypt('ChangeMe!')]);
# >>> $u->assignRole('super_admin');
```

### 9.1 Seed roles automatically (agent-runnable alternative)

If you need to seed without interactive tinker, create a one-off Artisan command or run:

```bash
php artisan db:seed --class=RolesSeeder --force
php artisan db:seed --class=SettingsSeeder --force
```

Then create the admin user via a raw SQL or a custom command:

```bash
php artisan tinker --execute="
  \$u = \App\Models\User::firstOrCreate(['email'=>'admin@example.com'], ['name'=>'Admin','password'=>bcrypt('ChangeMe!123')]);
  \$u->assignRole('super_admin');
  echo 'Admin user ready';
"
```

---

## 10. Upload the Lichess CSV (Manual)

The Lichess puzzle CSV is ~1 GB and is **not in the git repo** (it's gitignored). It must be uploaded to the server separately.

### 10.1 Upload via SCP

```bash
# From your local machine (where the CSV is):
scp /path/to/lichess_db_puzzle.csv root@YOUR_VPS_IP:/tmp/lichess_db_puzzle.csv
```

### 10.2 Move it into the app's storage volume

Coolify mounts persistent storage at a configured path. Find the app's data volume:

```bash
# On the VPS, find the Coolify volume for this app
docker volume ls | grep chess
# Example: coolify_chess-puzzle-challenge_storage
```

Then copy the CSV into the mounted storage path. The app expects it at `storage/app/lichess_db_puzzle.csv`.

Alternatively, use the Coolify **Terminal** tab on the app container:

```bash
# Inside the container
cd /app/storage/app
# The file must be placed here. If uploaded to /tmp on the host:
# Use docker cp from the host:
# docker cp /tmp/lichess_db_puzzle.csv <container_id>:/app/storage/app/lichess_db_puzzle.csv
```

### 10.3 Verify

In the admin panel → **Puzzles** → **Import CSV Data**, the path field should default to `lichess_db_puzzle.csv`. Click dispatch and check the queue worker logs process rows.

---

## 11. Domain & SSL

### 11.1 Point your domain to the VPS

In your DNS provider (e.g., Cloudflare, Namecheap):

```
A    staging.yourdomain.com    YOUR_VPS_IP
```

### 11.2 Configure the domain in Coolify

1. Coolify → Application → **Settings** → **Domains**.
2. Enter: `staging.yourdomain.com`.
3. Save.
4. Coolify automatically provisions a Let's Encrypt SSL certificate.

### 11.3 Verify

```bash
curl -I https://staging.yourdomain.com
# Expected: HTTP/2 200 with valid SSL certificate
```

### 11.4 Close the Coolify dashboard port (optional, for security)

After confirming everything works, you can restrict port 8000:

```bash
ufw delete allow 8000/tcp
# Or restrict to your IP only:
ufw delete allow 8000/tcp
ufw allow from YOUR_HOME_IP to any port 8000
```

---

## 12. CI/CD with GitHub Actions

Coolify can **auto-deploy on push** via a webhook — no GitHub Actions needed for the deploy itself. However, adding a GitHub Actions workflow gives you **pre-deploy checks** (tests, linting) so broken code never reaches the server.

### 12.1 Enable auto-deploy in Coolify

1. Coolify → Application → **Settings** → toggle **Auto Deploy** to ON.
2. Coolify registers a webhook on the GitHub repo. Every push to `main` triggers a rebuild.

### 12.2 Add a GitHub Actions CI workflow

Create `.github/workflows/ci.yml` in the repo:

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, json, pdo, pdo_sqlite
          coverage: none

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install PHP dependencies
        run: composer install --no-interaction --prefer-dist

      - name: Install Node dependencies
        run: npm ci

      - name: Build assets
        run: npm run build

      - name: Prepare environment
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Run tests
        run: php artisan test

      - name: Run Pint (code style)
        run: ./vendor/bin/pint --test
```

### 12.3 Add a deploy trigger via Coolify API (optional, for explicit control)

If you prefer to deploy only after CI passes (instead of Coolify's auto-deploy on push):

1. In Coolify → Application → **Settings** → copy the **Deploy Webhook URL**.
2. Add it as a GitHub secret: `COOLIFY_DEPLOY_WEBHOOK`.
3. Add a deploy job to the workflow:

```yaml
  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - name: Trigger Coolify deploy
        run: |
          curl -X POST "${{ secrets.COOLIFY_DEPLOY_WEBHOOK }}" \
            -H "Content-Type: application/json"
```

This ensures the server only rebuilds after tests pass. **Disable Coolify's auto-deploy** if you use this approach, to avoid double deploys.

---

## 13. Alternatives

### Option B: Laravel Forge + any VPS

- **Best for:** Laravel-focused teams who want zero-config queue/scheduler management.
- **Cost:** ~$12–$19/month (per server) + VPS cost.
- **How:** Buy Hostinger VPS → connect to Forge → Forge installs everything (Nginx, PHP, MySQL, Certbot). Push to GitHub → Forge auto-deploys.
- **Pros:** Deepest Laravel integration; handles `php artisan queue:work` and `schedule:run` automatically.
- **Cons:** Monthly subscription; less control over the stack.

### Option C: Ploi.io + any VPS

- **Best for:** Similar to Forge but cheaper.
- **Cost:** ~$5–$9/month + VPS cost.
- **How:** Same flow as Forge.
- **Pros:** Cheaper than Forge; good Laravel support.
- **Cons:** Smaller community than Forge.

### Option D: Deployer + GitHub Actions (no control panel)

- **Best for:** Teams who want full control and no monthly SaaS cost.
- **Cost:** VPS only.
- **How:** Write a `deploy.php` (Deployer recipe). GitHub Actions SSHes into the VPS and runs `dep deploy`.
- **Pros:** Free, version-controlled, fully customizable.
- **Cons:** You manage the server yourself (Nginx, PHP-FPM, MySQL, SSL, firewall, queue supervisor).

### Option E: Laravel Sail / Docker Compose on VPS

- **Best for:** Single-server, containerized deployments.
- **Cost:** VPS only.
- **How:** Use a production-tuned `docker-compose.yml` with PHP-FPM, Nginx, MySQL, Redis. Deploy via GitHub Actions + `docker compose up -d`.
- **Pros:** Reproducible; matches local Sail dev environment.
- **Cons:** More YAML to maintain; no GUI.

### Recommendation

| Scenario                                                   | Recommended                   |
|:---------------------------------------------------------- |:----------------------------- |
| Want a GUI, self-hosted, no subscription                   | **Coolify** (this guide)      |
| Want the most Laravel-native experience, don't mind paying | **Laravel Forge**             |
| Want cheapest, fully scripted, no GUI                      | **Deployer + GitHub Actions** |

---

## 14. Troubleshooting

### Build fails: "PHP version not detected"

Nixpacks may default to an older PHP. Pin it by creating a `nixpacks.toml` in the repo root:

```toml
[phases.setup]
nixpkgs = ["php83", "php83Packages.composer", "nodejs_20"]
```

### Migrations fail on first deploy

Ensure the MySQL database is linked to the app (step 6.2) and `DB_*` env vars are present. Run `php artisan migrate --force` manually in the Coolify terminal to see the error.

### Queue worker not processing jobs

1. Check `QUEUE_CONNECTION=database` is set.
2. Confirm the worker container is running (Coolify → Logs → worker).
3. Run `php artisan queue:failed` to see failed jobs.

### CSS/JS not loading (404)

The built assets live in `public/build/` which is gitignored. Ensure `npm run build` runs during the Coolify build phase (Nixpacks does this automatically for Laravel, but verify in build logs).

### Storage uploads 404

Run `php artisan storage:link` in the post-deployment command (step 7.1). For persistent storage across redeploys, mount a Coolify **persistent volume** at `/app/storage/app/private`.

### "No application encryption key has been specified"

Set `APP_KEY` in Coolify environment variables. Generate one locally:

```bash
php artisan key:generate --show
```

### Cron / scheduler not running

Verify the cron job or `schedule:work` worker is configured (step 7.3). Check `php artisan schedule:list` in the terminal.

---

## Quick Reference — Agent-Executable Checklist

An autonomous agent can run these in order. Each step is verifiable.

```
[ ] 1. SSH into VPS as root
[ ] 2. apt update && apt upgrade -y
[ ] 3. Install curl, git, ufw; configure firewall (22, 80, 443, 8000)
[ ] 4. Run: curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash
[ ] 5. Verify: docker ps | grep coolify
[ ] 6. Open http://VPS_IP:8000, create admin account
[ ] 7. Connect GitHub repo via Coolify dashboard
[ ] 8. Create application from salehuddin/Chess-Puzzle-Challenge (Nixpacks)
[ ] 9. Create MySQL database resource; link to app
[ ] 10. Set environment variables (APP_KEY, APP_URL, DB_*, STRIPE_*)
[ ] 11. Set post-deployment command: migrate --force, storage:link, config:cache
[ ] 12. Add queue worker: php artisan queue:work --tries=3
[ ] 13. Add scheduler: php artisan schedule:work
[ ] 14. Deploy; verify build logs are green
[ ] 15. Run: php artisan db:seed --class=RolesSeeder --force
[ ] 16. Create admin user via tinker
[ ] 17. Point DNS A record to VPS IP
[ ] 18. Set domain in Coolify; verify SSL
[ ] 19. Upload lichess_db_puzzle.csv via SCP/docker cp
[ ] 20. Trigger test CSV import; verify queue processes jobs
[ ] 21. (Optional) Add .github/workflows/ci.yml
[ ] 22. (Optional) Enable Coolify auto-deploy or webhook deploy
```






