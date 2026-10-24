FROM php:8.4-fpm

# Install dependensi sistem
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Copy Composer resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Optimasi Layer Cache: Copy file composer terlebih dahulu
COPY composer.json composer.lock ./

# Install dependensi PHP tanpa dev (produksi)
RUN composer install --no-scripts --no-autoloader 

# Copy seluruh source code backend
COPY . .

# Generate autoloader composer
RUN composer dump-autoload --optimize

# Set hak akses folder storage dan cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]