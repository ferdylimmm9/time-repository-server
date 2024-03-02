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
    unzip

# Install PostgreSQL extension
RUN apt-get install -y libpq-dev && docker-php-ext-install pdo_pgsql

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Remove the default index.html
# RUN rm /var/www/html/index.html

# Copy existing application directory contents
COPY . /var/www

# Copy the Apache configuration file into the container to serve the Laravel app
# COPY .docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache mod_rewrite for URL rewriting
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Set permissions for the Laravel app
RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www/storage && \
    chmod -R 755 /var/www/bootstrap/cache

# Start Apache service
CMD ["apache2-foreground"]
