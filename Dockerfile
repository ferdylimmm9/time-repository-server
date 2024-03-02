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
# Ignoring platform reqs might not be the best approach for all scenarios,
# as it might lead to installing packages that cannot be executed due to missing PHP extensions.
# The --ignore-platform-req=ext-gd has been removed to prevent ignoring important requirements.
# Consider explicitly installing or enabling all required PHP extensions.
RUN composer clear-cache
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --no-ansi --no-scripts --no-progress --prefer-dist

# Copy .env.example to .env and generate app key
# The artisan command is commented out to avoid failures during Docker build
# due to potential absence of environment-specific configurations in .env.
# Consider running the key generation command outside the Dockerfile or after ensuring .env is correctly set up.
# RUN cp .env.example .env && php artisan key:generate

# Change ownership of our applications
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
