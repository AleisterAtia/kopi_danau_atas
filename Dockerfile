# syntax=docker/dockerfile:1

# ──────────────────────────────────────────────────────────────────
# Stage 1 — build the front-end assets (Vite) with Node.
# ──────────────────────────────────────────────────────────────────
FROM node:20-alpine AS assets
WORKDIR /app

# Vite bakes these into the JS bundle at build time — a runtime .env change
# alone won't take effect, the image must be rebuilt. Must be the PUBLIC
# wss:// address (see docs/deployment.md), not the internal REVERB_HOST used
# server-side.
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY \
    VITE_REVERB_HOST=$VITE_REVERB_HOST \
    VITE_REVERB_PORT=$VITE_REVERB_PORT \
    VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ──────────────────────────────────────────────────────────────────
# Stage 2 — PHP runtime (CLI: serves the app, runs the queue worker
# and the scheduler — see docker-compose.yml).
# ──────────────────────────────────────────────────────────────────
FROM php:8.3-cli AS app

# System libraries required by the PHP extensions installed below.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip default-mysql-client \
        libzip-dev libpng-dev libonig-dev libicu-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring gd zip bcmath intl exif pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer from the official image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first for better layer caching. Scripts/autoloader
# are deferred until the full source is present (artisan isn't here yet).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader \
        --no-interaction --prefer-dist --no-progress

# Application source + the assets built in stage 1.
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-scripts --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8000
ENTRYPOINT ["entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
