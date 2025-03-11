FROM php:8.1-fpm-alpine3.14

ARG BUILD_ENV=production
ARG USER_ID=
ARG GROUP_ID=

WORKDIR /var/www

RUN apk add --no-cache multirun wget gnupg git libzip-dev unzip shadow openssl-dev $PHPIZE_DEPS

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
#RUN echo "memory_limit = 1G" > "$PHP_INI_DIR/conf.d/memory-limit.ini"

RUN pecl install pcov && \
    pecl install redis && \
    docker-php-ext-enable pcov redis

RUN docker-php-ext-install bcmath opcache pcntl pdo_mysql sockets

COPY --from=caddy:2.6.1-alpine /usr/bin/caddy /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin

COPY composer.json composer.lock ./
RUN composer install --no-autoloader --no-dev --no-interaction --no-progress

COPY . ./
RUN composer dump-autoload --optimize

RUN if [ "$BUILD_ENV" != "production" ] && [ -n "$USER_ID" ] && [ -n "$GROUP_ID" ] ; \
    then usermod -u $USER_ID www-data && groupmod -g $GROUP_ID www-data ; \
    fi

RUN chgrp -R www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

ENTRYPOINT ["./entrypoint.sh"]
