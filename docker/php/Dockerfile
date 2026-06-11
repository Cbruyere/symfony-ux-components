FROM php:8.4-fpm-bookworm

ARG UID=1000
ARG GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="/app/vendor/bin:${PATH}"

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    acl \
    ca-certificates \
    curl \
    git \
    gnupg \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    zip \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-install -j"$(nproc)" intl opcache pdo_pgsql zip \
    && pecl install apcu xdebug \
    && docker-php-ext-enable apcu xdebug \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && groupmod -g "${GID}" www-data \
    && usermod -u "${UID}" -g "${GID}" www-data \
    && mkdir -p /app /var/www/.composer /var/www/.npm \
    && chown -R www-data:www-data /app /var/www \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# COPY .docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
# COPY .docker/php/www.conf /usr/local/etc/php-fpm.d/zz-app.conf

WORKDIR /app

USER www-data