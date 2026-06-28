#!/bin/sh
set -e

# Wait for the database to accept TCP connections before doing anything that
# touches it (compose starts db + app together).
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
    until php -r "exit(@fsockopen(getenv('DB_HOST'), (int)(getenv('DB_PORT') ?: 3306)) ? 0 : 1);" 2>/dev/null; do
        sleep 2
    done
    echo "Database is up."
fi

# Only the web container runs migrations (RUN_MIGRATIONS=true in compose) so the
# queue/scheduler containers don't race it.
if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force
    php artisan storage:link || true
fi

# Cache config for a faster, reproducible runtime (best-effort).
php artisan config:cache || true

exec "$@"
