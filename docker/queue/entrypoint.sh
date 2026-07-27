#!/bin/sh
set -eu

wait_for_port() {
    host="$1"
    port="$2"
    label="$3"

    until php -r "exit(@fsockopen('${host}', ${port}) ? 0 : 1);" >/dev/null 2>&1; do
        echo "Waiting for ${label} at ${host}:${port}..."
        sleep 1
    done
}

wait_for_port "${DB_HOST:-mysql}" "${DB_PORT:-3306}" "mysql"
wait_for_port "${REDIS_HOST:-redis}" "${REDIS_PORT:-6379}" "redis"

exec php artisan queue:work --sleep=1 --tries=3 --timeout=90
