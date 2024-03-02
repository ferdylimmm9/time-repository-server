# Use the official PHP image with Apache
FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev # Added for PostgreSQL support

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Increase Composer memory limit and add verbose output for troubleshooting
# Clear Composer's cache before install
RUN COMPOSER_MEMORY_LIMIT=-1 composer clear-cache
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --no-ansi --no-scripts --no-progress --prefer-dist --ignore-platform-req=ext-gd

# Copy .env.example to .env and generate app key
# The artisan command might fail if .env file is not properly configured for your environment
# Consider generating the key manually or ensuring your .env configuration is correct
RUN cp .env.example .env && php artisan key:generate

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
