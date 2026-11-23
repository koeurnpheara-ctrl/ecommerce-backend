FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash git unzip icu-dev libzip-dev oniguruma-dev \
    freetype-dev libjpeg-turbo-dev libpng-dev \
    mysql-client nginx supervisor

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
       bcmath exif gd intl mbstring opcache pdo_mysql zip

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 9000
CMD ["php-fpm"]
