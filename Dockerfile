# syntax=docker/dockerfile:1.7

FROM php:8.3-fpm-alpine

WORKDIR /var/www

RUN apk add --no-cache \
        bash \
        curl \
        git \
        icu-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        unzip \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        mbstring \
        pdo_mysql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del --no-network $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY . /var/www

RUN chown -R www-data:www-data /var/www \
    && chmod -R ug+rwx storage bootstrap/cache \
    && chmod +x /var/www/docker/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/var/www/docker/entrypoint.sh"]
CMD ["php-fpm"]
