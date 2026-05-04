FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-scripts --no-interaction
COPY . .
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views
RUN composer dump-autoload --optimize

FROM php:8.2-fpm
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev zlib1g-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev libpq-dev unzip git zip postgresql-client \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring zip exif pcntl bcmath \
    && rm -rf /var/lib/apt/lists/*

# Copy application from vendor stage
WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

EXPOSE 10000
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT}"]
