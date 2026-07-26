FROM php:8.3-fpm-bookworm

ARG WWWGROUP=1000

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        zip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libmemcached-dev \
        zlib1g-dev \
        pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        pcntl \
        pdo_mysql \
        sockets \
        zip \
    && pecl install redis memcached \
    && docker-php-ext-enable redis memcached \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN usermod -u ${WWWGROUP} www-data \
    && groupmod -g ${WWWGROUP} www-data

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-crmigrejas.ini

CMD ["php-fpm"]
