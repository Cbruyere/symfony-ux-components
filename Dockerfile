########################
# 1️⃣ Composer deps (bundle + demo, via path repository)
########################
FROM composer:2 AS composer-builder

WORKDIR /app

COPY . .
RUN cd demo && composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


########################
# 2️⃣ PHP / Symfony (demo app)
########################
FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl opcache pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN cat > /etc/apache2/sites-available/000-default.conf <<'EOF'
<VirtualHost *:80>
    ServerName ux-components-demo.local
    DocumentRoot /var/www/html/demo/public

    <Directory /var/www/html/demo/public>
        AllowOverride None
        Require all granted
        FallbackResource /index.php
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

WORKDIR /var/www/html

COPY . .
COPY --from=composer-builder /app/demo/vendor ./demo/vendor

RUN mkdir -p demo/var/cache demo/var/log \
    && chown -R www-data:www-data demo/var demo/public \
    && chmod -R 775 demo/var

ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN cd demo \
    && composer dump-autoload --classmap-authoritative --no-dev \
    && php bin/console importmap:install --env=prod \
    && php bin/console asset-map:compile --env=prod \
    && php bin/console cache:clear --env=prod --no-debug \
    && php bin/console cache:warmup --env=prod --no-debug

EXPOSE 80
