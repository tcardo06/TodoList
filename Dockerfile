FROM php:7.2-cli

RUN sed -i 's|deb.debian.org|archive.debian.org|g; s|security.debian.org|archive.debian.org|g' /etc/apt/sources.list \
    && apt-get -o Acquire::Check-Valid-Until=false -o Acquire::Check-Date=false update \
    && apt-get install -y \
        git \
        unzip \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        zip \
    && docker-php-ext-install intl pdo_mysql mbstring xml zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/todo
