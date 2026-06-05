FROM php:8.4-fpm-alpine

WORKDIR /var/www/backend

RUN apk add --no-cache \
        bash \
        curl \
        git \
        icu-dev \
        libpq-dev \
        libzip-dev \
        linux-headers \
        oniguruma-dev \
        postgresql-client \
        zip \
        unzip \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        sockets \
        zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer