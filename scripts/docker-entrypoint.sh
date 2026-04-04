#!/bin/sh
set -e

cd /var/www/html

# I worker del built-in server di `php artisan serve` possono non ereditare tutte le variabili
# imposte da Docker Compose; forziamo l'host DB se siamo in rete compose (override .env con 127.0.0.1).
export DB_HOST="${DB_HOST:-db}"

PORT="${PORT:-10000}"
ROLE="${1:-serve}"
if [ "$#" -gt 0 ]; then
  shift
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-dev --optimize-autoloader --no-interaction --no-progress
fi

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

rm -f bootstrap/cache/*.php 2>/dev/null || true

php artisan storage:link --force --no-interaction 2>/dev/null || true

if [ "${APP_ENV:-local}" = "production" ]; then
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
    php artisan event:cache --no-interaction 2>/dev/null || true
fi

case "$ROLE" in
  migrate)
    exec php artisan migrate --force
    ;;
  worker)
    exec php artisan queue:work --verbose --tries=3 --timeout=90
    ;;
  serve)
    exec php artisan serve --host=0.0.0.0 --port="${PORT}"
    ;;
  *)
    exec "$ROLE" "$@"
    ;;
esac
