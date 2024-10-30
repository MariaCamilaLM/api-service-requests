# Stage 1: Build dependencies with Composer
FROM php:8.2-fpm as build

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy existing application files to the working directory
COPY . .

# Install PHP dependencies using Composer
RUN composer install --optimize-autoloader --no-dev

# Stage 2: Build the production image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Copy the application files and vendor directory from the build stage
COPY --from=build /var/www /var/www

# Copy the .env.example to .env (You may replace this with a proper .env file in your deployment)
COPY .env.example .env

# Set permissions for Laravel storage and cache directories
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Set the entrypoint for PHP-FPM
CMD ["php-fpm"]
