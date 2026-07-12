#!/bin/sh
set -e

# Mirror the production entrypoint so dev behaves like prod on first boot.
mkdir -p /app/storage/app/private/livewire-tmp
mkdir -p /app/storage/framework/cache/data
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/logs
mkdir -p /app/bootstrap/cache
mkdir -p /app/public

ln -sf /app/storage/app/public /app/public/storage

chown -R www-data:www-data /app/storage /app/bootstrap/cache

# vendor/ and node_modules/ are anonymous volumes in docker-compose, so they
# persist across restarts but may be empty on first run. Install if missing.
if [ ! -f /app/vendor/autoload.php ]; then
    echo "[entrypoint-dev] Installing Composer dependencies (with dev)..."
    composer install --no-interaction
fi

if [ ! -d /app/node_modules/.bin ]; then
    echo "[entrypoint-dev] Installing Node dependencies..."
    npm ci
fi

# Build Vite assets once so a manifest exists. Without this, Laravel throws
# ViteManifestNotFoundException on cold boot (queue worker / first request)
# because vite dev hasn't written public/hot yet. With the manifest present,
# every Laravel process boots fine; once vite dev is up, public/hot takes
# precedence and HMR serves from the dev server.
if [ ! -f /app/public/build/manifest.json ]; then
    echo "[entrypoint-dev] Building Vite manifest (one-time)..."
    npm run build
fi

exec "$@"
