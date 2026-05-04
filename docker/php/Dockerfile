FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies (minimal for quiz app)
RUN apt-get update && apt-get install -y \
    build-essential \
    locales \
    zip \
    unzip \
    git \
    curl \
    wget \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    postgresql-client \
    libpq-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    bcmath \
    ctype \
    fileinfo \
    xml \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Create app user
RUN useradd -m -u 1000 appuser && chown -R appuser:appuser /var/www

# Switch to app user
USER appuser

# Allow git operations in /var/www
RUN git config --global --add safe.directory /var/www

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]
