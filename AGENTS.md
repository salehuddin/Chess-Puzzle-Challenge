# Workflow Guide — Local → GitHub → Coolify

This is the canonical workflow to follow for this project. It keeps local
development, CI, and production deployed on Coolify in parity.

## Stack parity (local == production)

| Layer        | Both local & production                         |
| :----------- | :---------------------------------------------- |
| PHP runtime  | `php:8.5-fpm` (Docker `base` stage)             |
| Extensions   | `pdo_mysql bcmath gd zip intl mbstring …`       |
| php.ini      | `docker/php/php.ini` (25M upload / 256M memory) |
| Web server   | nginx + PHP-FPM via supervisord                 |
| Database     | MySQL 8                                         |
| Laravel cfg  | session/cache/queue=database, fs=local, livewire-tmp |

Local differences (intentional): `APP_ENV=local`, `APP_DEBUG=true`, Vite HMR
on `:5173`, dev composer deps installed, scheduler via `schedule:work`.

---

## Phase 1 — Local development

### Start the stack

```bash
docker compose up -d --build
```

First run installs composer + node deps (anonymous volumes persist them).
Starts: nginx, php-fpm, vite (HMR), queue worker, scheduler.

App: http://localhost:8080 · Vite HMR: http://localhost:5173

### Run any artisan/composer/npm command

Always inside the container so deps match production's Linux environment:

```bash
docker compose exec app php artisan <command>
docker compose exec app composer <command>
docker compose exec app npm <command>
```

### First-time setup (fresh DB)

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
docker compose exec app php artisan db:seed --class=RolesSeeder --force
docker compose exec app php artisan db:seed --class=SettingsSeeder --force
```

### Daily loop

1. Edit code on the host (bind-mount reflects into container instantly).
2. Vite HMR reloads the browser; Laravel `LOG_STACK=single` tailed via:
   ```bash
   docker compose logs -f app
   ```
3. Stop / restart:
   ```bash
   docker compose down        # stop (MySQL data persists in named volume)
   docker compose up -d       # resume
   docker compose up -d --build  # rebuild after Dockerfile/php.ini change
   ```

### If Docker isn't available

Native fallback (loses runtime parity): set `DB_HOST=127.0.0.1` in `.env`,
run `composer dev`. Use only when Docker can't run.

---

## Phase 2 — Before commit (run these locally)

```bash
docker compose exec app ./vendor/bin/pint --test     # code style
docker compose exec app php artisan test             # test suite
```

Do not commit if either fails. Fix, re-run, then proceed.

### Files never to commit

- `.env` (gitignored) — contains local DB creds; never real secrets
- `package-lock.json` changes from inside the container (use `npm ci`, not `npm install`)
- `storage/` logs, `public/build/` artifacts, `public/hot`

---

## Phase 3 — Commit & push to GitHub

```bash
git status                            # review changed files
git diff                              # review the diff
git add <intended files only>
git commit -m "<imperative summary matching repo style>"
git push origin main
```

Commit message style: short imperative mood (e.g. `Fix medal stock movement on fulfillment delete`).

---

## Phase 4 — Continuous Integration (GitHub Actions)

`.github/workflows/ci.yml` runs on every push to `main` and every PR:

1. Setup PHP 8.5 + Node 20
2. `composer install`
3. `npm ci && npm run build`
4. `php artisan key:generate` (sqlite for test DB)
5. `php artisan test`
6. `./vendor/bin/pint --test`

If any step fails, fix locally and push again. Do not proceed to deploy.

### Deploy job

After the `test` job passes (push to `main` only), the `deploy` job sends a
POST to the Coolify deploy webhook (`vars.COOLIFY_DEPLOY_WEBHOOK` repo
variable). Coolify rebuilds the Docker image from the `Dockerfile` and rolls
the new container.

If `COOLIFY_DEPLOY_WEBHOOK` is unset, the deploy job is skipped silently —
Coolify's own auto-deploy-on-push (if enabled) handles it instead.

---

## Phase 5 — Coolify production deploy

Coolify builds the `production` stage of `Dockerfile`:
- `php:8.5-fpm` base + nginx + supervisord
- `composer install --no-dev --optimize-autoloader`
- `npm ci && npm run build` (bakes assets into the image)
- Copies `docker/php/php.ini`, `docker/entrypoint.sh`

Post-deployment command (set in Coolify app settings, see `deploy-coolify.ps1`):
```
php artisan migrate --force && php artisan storage:link && php artisan db:seed --class=RolesSeeder --force && php artisan db:seed --class=SettingsSeeder --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Production env vars are set in Coolify dashboard (not in repo). The
`deploy-coolify.ps1` script provisions them via the Coolify API, including:
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://chesspuzzlechallenge.com`
- `DB_*` (auto-injected by Coolify from the linked MySQL resource)
- `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`
- `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=livewire-tmp` (required for Filament uploads)
- `SANDBOX_PAYMENT_MODE=true` (toggle off when Stripe goes live)

### Watch the deploy

1. Coolify dashboard → Deployments → latest → watch build logs (5–10 min).
2. Green "Deployed" status = success.
3. If red: read the failing step, fix locally, push again.

---

## Phase 6 — Verify production

```bash
curl -I https://chesspuzzlechallenge.com        # expect HTTP 200/302
curl -I https://chesspuzzlechallenge.com/up      # expect HTTP 200 (health check)
```

Then in the browser:
- https://chesspuzzlechallenge.com — homepage renders
- https://chesspuzzlechallenge.com/admin/login — Filament login page
- Log in, browse an admin resource, confirm no 500s

If a Filament upload 500s, check (per `DEPLOYMENT.md` §8.1 & §14):
- `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=livewire-tmp` in Coolify env
- `php artisan config:cache` re-run after env changes
- `storage/app/private/livewire-tmp` exists and is writable (entrypoint creates it)

---

## Quick reference — the full loop

```
[local]  docker compose up -d --build
         docker compose exec app php artisan test
         docker compose exec app ./vendor/bin/pint --test
         git add <files> && git commit -m "..." && git push origin main
[github] CI runs (test + pint) → on pass, deploy job calls Coolify webhook
[coolify] builds production Docker image → runs post-deploy cmd → live
[verify] curl https://chesspuzzlechallenge.com/up → HTTP 200
```

## Troubleshooting cheat sheet

| Symptom | First check |
| :--- | :--- |
| `docker compose up` fails to build | Docker Desktop running? `docker compose config` validates |
| Queue worker crashes on boot | `public/build/manifest.json` missing → entrypoint builds it; if still failing, run `docker compose exec app npm run build` |
| Filament upload 500 | `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` set? `config:cache` re-run? See DEPLOYMENT.md §14 |
| Local app 500 on `/admin` | Migrations run? `docker compose exec app php artisan migrate --force` |
| Production deploy stuck | Coolify dashboard → Deployments → read logs; common cause = missing env var or DB not linked |
| CSS/JS 404 on production | `npm run build` didn't run in build stage → check Dockerfile `build` stage |
| Scheduler not firing | Local: `[program:scheduler]` in Dockerfile dev stage. Prod: cron job per DEPLOYMENT.md §7.3 |
