#!/usr/bin/env bash
set -euo pipefail

if [[ -n "${DB_HOST:-}" ]]; then
    echo "→ waiting for postgres at ${DB_HOST}:${DB_PORT:-5432}..."
    until pg_isready -h "${DB_HOST}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" -q; do
        sleep 1
    done
fi

if [[ ! -f .env ]]; then
    cp .env.docker .env
fi

if ! grep -qE '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --ansi
fi

php artisan migrate --force --ansi

if [[ "$(php artisan tinker --execute='echo App\Models\Vehicle::count();' 2>/dev/null)" == "0" ]]; then
    php artisan db:seed --force --ansi
fi

php artisan config:cache --ansi
php artisan route:cache --ansi
php artisan view:cache --ansi

exec "$@"