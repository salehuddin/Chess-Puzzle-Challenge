FROM php:8.3-fpm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl wget git unzip \
    libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev \
    libzip-dev libonig-dev libxml2-dev \
    libcurl4-openssl-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql bcmath gd zip intl pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM base AS build

COPY --from=node:22 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22 /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm && \
    ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev
RUN npm ci && npm run build

FROM base AS production

WORKDIR /app
COPY --from=build /app /app
COPY --from=build /app/public/build /app/public/build

RUN mkdir -p /app/storage/framework/cache \
             /app/storage/framework/sessions \
             /app/storage/framework/views \
             /app/bootstrap/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache

RUN php artisan storage:link 2>/dev/null || true

EXPOSE 9000
CMD ["php-fpm"]
