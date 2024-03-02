# Use the official PHP image with Apache
FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Clear cache to keep the image size down
# RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory in the container
COPY . /var/www/html
WORKDIR /var/www/html

# Optimizing Docker Layers for Composer
# Copy composer.json and composer.lock files
# COPY composer.json composer.lock ./

# Run composer install separately to leverage Docker cache
# Increase Composer memory limit
# Use verbose output to diagnose problems
# If this step fails, the verbose output will help pinpoint the issue
# RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --no-ansi --no-scripts --no-progress --prefer-dist -vvv

# Copy the application code to the container
# COPY . .

# It's better to run php artisan key:generate after the container starts
# and environment variables are properly set up. This command is dependent on your .env file.
# Consider using Docker entrypoint scripts or manual execution for this step.

# Change ownership of our application
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 to the outside world
EXPOSE 80
