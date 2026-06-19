# Deployment Guide — Hostinger Cloud Hosting (Testing/Staging)

Deploy the app to **Hostinger Cloud Hosting** for testing. This is managed hosting (hPanel, no root, no Docker) — Laravel runs as plain PHP + MySQL, the same way WordPress does.

> **When to use this:** Quick staging/preview for your team. No VPS setup, no Docker.  
> **When NOT to use this:** Production with heavy CSV imports or high traffic. Use the VPS + Coolify guide (`DEPLOYMENT.md`) for that.

---

## How It Differs from the VPS Guide

| Concern | VPS + Coolify | Cloud Hosting |
| :--- | :--- | :--- |
| Server control | Full root | hPanel only |
| Docker | Yes | No |
| Queue workers | Long-running daemon | Cron-based (every minute) |
| Deployment | Auto-build from Git | Git clone + manual `composer`/`npm` |
| SSL | Let's Encrypt via Coolify | Free SSL via hPanel |
| Cost | VPS plan | Already have it |

---

## Prerequisites

| Requirement | Details |
| :--- | :--- |
| Hostinger Cloud Hosting plan | Any plan with SSH access (most Cloud plans include it) |
| PHP version | 8.3 or higher (set in hPanel) |
| MySQL | One database (created via hPanel) |
| Domain/subdomain | Optional; can use the free temporary domain for testing |
| GitHub repo | https://github.com/salehuddin/Chess-Puzzle-Challenge |

---

## Step-by-Step

### Step 1 — Prepare PHP version in hPanel

1. Log into **hPanel** → **Advanced** → **PHP Configuration**.
2. Set PHP version to **8.3** (or higher).
3. Verify: the Laravel app requires PHP 8.3+ (`composer.json` enforces `^8.3`).

### Step 2 — Enable PHP extensions

1. hPanel → **Advanced** → **PHP Configuration** → **PHP Options** (or **Extensions**).
2. Make sure these extensions are enabled:
   - `mbstring`
   - `pdo_mysql`
   - `xml`
   - `ctype`
   - `json`
   - `tokenizer`
   - `curl`
   - `fileinfo`
   - `bcmath`
   - `gd`
   - `openssl`

> Most are enabled by default on Hostinger. Just verify `pdo_mysql` and `gd` are on.

### Step 3 — Create a MySQL database

1. hPanel → **Databases** → **MySQL Databases**.
2. **Create a new database**:
   - Database name: `u123_chess_puzzle` (Hostinger prefixes with your username)
   - Username: `u123_chess_app`
   - Password: `<strong password>` — save this!
3. Note the **database host** (usually `localhost` on Hostinger Cloud).

### Step 4 — Create a subdomain (optional but recommended)

Using a subdomain keeps the app separate from any WordPress sites on the same hosting.

1. hPanel → **Domains** → **Subdomains**.
2. Create: `staging.yourdomain.com` (or use the Hostinger free temporary domain).
3. Set the **document root** to: `staging` (we'll deploy the app into this folder; Laravel's `public/` will be the doc root — see Step 7 for the exact path).

### Step 5 — Get the code onto the server

You have two options:

#### Option A — SSH + Git clone (recommended)

1. hPanel → **Advanced** → **SSH Access** → enable SSH if not already. Note your SSH credentials.
2. SSH into the server:
   ```bash
   ssh u123@your-server-ip
   ```
3. Navigate to the domain root:
   ```bash
   cd ~/domains/staging.yourdomain.com
   # Or if using public_html:
   cd ~/public_html/staging
   ```
4. Clone the repo:
   ```bash
   git clone https://github.com/salehuddin/Chess-Puzzle-Challenge.git .
   ```
   > The `.` at the end clones into the current directory (must be empty).

#### Option B — hPanel Git integration

1. hPanel → **Files** → **Git**.
2. **Clone a repository** → enter the GitHub URL.
3. Set the deployment path to your subdomain folder.
4. Hostinger will clone the repo. You can set up auto-pull on push later.

### Step 6 — Install dependencies and build assets

Via SSH (or hPanel → **Advanced** → **Terminal**):

```bash
cd ~/domains/staging.yourdomain.com
# Or your actual deployment path

# Install PHP dependencies (no dev packages for a smaller footprint)
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend assets
npm ci
npm run build
```

> **If `composer` is not found:** Hostinger should have it, but if not, run:
> ```bash
> curl -sS https://getcomposer.org/installer | php
> php composer.phar install --no-dev --optimize-autoloader
> ```

> **If `npm` is not found:** Hostinger Cloud may not have Node.js. In that case:
> 1. Build assets **locally** on your machine: `npm run build`
> 2. Upload the `public/build/` folder to the server via File Manager or SCP:
>    ```bash
>    scp -r public/build u123@your-server-ip:~/domains/staging.yourdomain.com/public/
>    ```
> 3. The built assets are gitignored, so they must be uploaded separately.

### Step 7 — Set the document root to Laravel's `public/`

Laravel's entry point is the `public/index.php` file. The web server must serve from that folder.

1. hPanel → **Domains** → your subdomain → **Document Root**.
2. Change it from `staging` to:
   ```
   domains/staging.yourdomain.com/public
   ```
   Or if using `public_html`:
   ```
   public_html/staging/public
   ```
3. Save.

> If hPanel doesn't let you set the doc root inside a subfolder easily, alternatively deploy the app to the subdomain root and the `public/` folder becomes the doc root automatically. The exact path depends on your Hostinger panel version.

### Step 8 — Configure the environment file

Via SSH or Terminal:

```bash
cd ~/domains/staging.yourdomain.com
cp .env.example .env
php artisan key:generate
```

Now edit `.env` with the database details from Step 3:

```bash
nano .env
```

Set these values:

```env
APP_NAME="Chess Puzzle Challenge"
APP_ENV=local
APP_KEY=<auto-generated by key:generate>
APP_DEBUG=true
APP_URL=https://staging.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123_chess_puzzle
DB_USERNAME=u123_chess_app
DB_PASSWORD=<your database password>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

# Sandbox payment mode (no real Stripe charges)
SANDBOX_PAYMENT_MODE=true

# Stripe test keys (only needed if testing non-sandbox checkout)
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

> **For pure testing**, `SANDBOX_PAYMENT_MODE=true` lets you "pay" without Stripe. Leave Stripe keys empty.

### Step 9 — Run migrations and seeders

```bash
php artisan migrate --force
php artisan storage:link

# Seed roles and settings (required for admin login)
php artisan db:seed --class=RolesSeeder --force
php artisan db:seed --class=SettingsSeeder --force
```

### Step 10 — Create an admin user

```bash
php artisan tinker --execute="
  \$u = \App\Models\User::firstOrCreate(
    ['email' => 'admin@chess.test'],
    ['name' => 'Admin', 'password' => bcrypt('ChangeMe!123')]
  );
  \$u->assignRole('super_admin');
  echo 'Admin user created: admin@chess.test';
"
```

> Change the email and password after first login.

### Step 11 — Set up the scheduler (cron job)

Laravel's task scheduler needs a cron job that runs every minute.

1. hPanel → **Advanced** → **Cron Jobs**.
2. Add a new cron job:
   - **Schedule:** Every minute (`* * * * *`)
   - **Command:**
     ```bash
     cd /home/u123/domains/staging.yourdomain.com && php artisan schedule:run >> /dev/null 2>&1
     ```
   > Replace `u123` with your actual Hostinger username and the path with your actual deployment path.

### Step 12 — Set up the queue worker (cron-based)

Shared hosting doesn't allow long-running daemon processes. Instead, run the queue worker via cron — it processes all pending jobs and exits.

1. Add another **cron job** in hPanel:
   - **Schedule:** Every minute (`* * * * *`)
   - **Command:**
     ```bash
     cd /home/u123/domains/staging.yourdomain.com && php artisan queue:work --stop-when-empty --max-time=50 --tries=1 >> /dev/null 2>&1
     ```

> **How this works:** Every minute, the cron launches the worker. It processes any queued jobs (like CSV imports), then exits when the queue is empty or after 50 seconds. The next minute, it starts again. For testing, this is perfectly fine.

> **For very simple testing** (no CSV imports): you can skip this and just set `QUEUE_CONNECTION=sync` in `.env`. Jobs will run immediately during the web request. Only use `sync` if you're not testing CSV imports, since large imports will timeout.

### Step 13 — Enable SSL

1. hPanel → **Security** → **SSL**.
2. Select your subdomain.
3. Enable **Free Let's Encrypt SSL** (auto-renews).

### Step 14 — Upload the Lichess CSV (if testing imports)

The CSV is ~1 GB and gitignored. Upload it via SCP or File Manager:

```bash
# From your local machine:
scp /path/to/lichess_db_puzzle.csv u123@your-server-ip:~/domains/staging.yourdomain.com/storage/app/
```

Or via hPanel **File Manager**:
1. Navigate to `storage/app/`.
2. Upload `lichess_db_puzzle.csv`.

> **Heads up:** The 1 GB CSV may exceed Hostinger's File Manager upload limit. Use SSH/SCP for large files. If your Cloud Hosting plan has limited storage, consider importing a small subset locally and exporting a smaller CSV.

### Step 15 — Verify everything

1. **Visit the site:** `https://staging.yourdomain.com` — should show the welcome page or redirect to login.
2. **Admin panel:** `https://staging.yourdomain.com/admin` — log in with the admin user from Step 10.
3. **Test a CSV import:** Go to Puzzles → Import CSV Data → set a small limit (e.g., 100) → dispatch. Check that the cron-based queue worker picks it up within a minute.
4. **Test puzzle player:** Enroll in a challenge (sandbox payment) → play through puzzles.

---

## Updating the App

When you push new code to GitHub, update the server:

```bash
# SSH in
ssh u123@your-server-ip
cd ~/domains/staging.yourdomain.com

# Pull latest code
git pull origin main

# Update dependencies if changed
composer install --no-dev --optimize-autoloader

# Rebuild assets if frontend changed
npm ci && npm run build

# Run new migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> If Node.js isn't available on the server, build assets locally and upload `public/build/` via SCP after each frontend change.

### Optional: Auto-deploy on push via hPanel Git

If you used hPanel's Git integration (Step 5, Option B), you can enable **auto-deploy** so the server pulls on every push. You still need to run `composer install` and `npm run build` manually after — hPanel's Git only does a `git pull`, not the build steps.

---

## Limitations of Cloud Hosting for This App

| Feature | Works? | Notes |
| :--- | :--- | :--- |
| Web pages / admin panel | Yes | Full functionality |
| Database / migrations | Yes | Standard MySQL |
| Puzzle player | Yes | Client-side JS, no server load |
| Stripe checkout | Yes | Use sandbox mode for testing |
| Cron scheduler | Yes | Via hPanel cron jobs |
| Queue worker | Partial | Cron-based (1-min granularity, 50s max per run) |
| Large CSV import (100k+) | Risky | May timeout. Use small limits (100–1000 rows) for testing |
| Queue job timeout | Limited | `ImportLichessPuzzlesJob` sets 3600s timeout — cron worker only runs 50s. For large imports, use the VPS guide instead. |
| Persistent storage | Yes | Uploaded artwork persists in `storage/app/private/` |

> **Bottom line:** Cloud Hosting is great for letting your team test the UI, admin panel, and gameplay. For testing large CSV imports or production, use the VPS + Coolify guide (`DEPLOYMENT.md`).

---

## Quick Reference — Agent-Executable Checklist

```
[ ] 1. hPanel: set PHP to 8.3, enable required extensions
[ ] 2. hPanel: create MySQL database + user, save credentials
[ ] 3. hPanel: create subdomain, note the path
[ ] 4. SSH in; git clone repo into subdomain folder
[ ] 5. composer install --no-dev --optimize-autoloader
[ ] 6. npm ci && npm run build (or build locally + upload public/build/)
[ ] 7. hPanel: set document root to the Laravel public/ folder
[ ] 8. cp .env.example .env && php artisan key:generate
[ ] 9. Edit .env: DB credentials, APP_URL, SANDBOX_PAYMENT_MODE=true
[ ] 10. php artisan migrate --force
[ ] 11. php artisan storage:link
[ ] 12. php artisan db:seed --class=RolesSeeder --force
[ ] 13. php artisan db:seed --class=SettingsSeeder --force
[ ] 14. Create admin user via tinker
[ ] 15. hPanel: add cron for schedule:run (every minute)
[ ] 16. hPanel: add cron for queue:work --stop-when-empty (every minute)
[ ] 17. hPanel: enable free SSL for the subdomain
[ ] 18. (Optional) SCP upload lichess_db_puzzle.csv to storage/app/
[ ] 19. Verify: site loads, admin login works, puzzle player works
[ ] 20. (Optional) Set up auto-pull via hPanel Git
```
