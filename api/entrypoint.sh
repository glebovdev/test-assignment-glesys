#!/usr/bin/env sh
set -e

role=${CONTAINER_ROLE:-artisan}
env=${APP_ENV:-production}
arguments=$@

if [ "$env" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
fi

if [ "$env" = "testing" ]; then
    composer install --no-interaction --no-progress
    php -dpcov.enabled=1 -dpcov.directory=. -dpcov.exclude="~vendor~" ./vendor/bin/phpunit
    ./vendor/bin/pint --test
elif [ "$role" = "artisan" ]; then
    php artisan $arguments
elif [ "$role" = "api" ]; then
    exec multirun -v "php-fpm" "caddy run"
elif [ "$role" = "scheduler" ]; then
    while :; do
      php artisan schedule:run --verbose --no-interaction &
      sleep 60
    done
elif [ "$role" = "queue-worker" ]; then
    exec php artisan queue:work --tries=3
else
    echo "Could not match the container role \"$role\""
    exit 1
fi
