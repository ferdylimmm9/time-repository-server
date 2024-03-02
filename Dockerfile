# Use the official PHP image with Apache
FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update -qq && apt-get install -y --no-install-recommends \
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
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory in the container
COPY . /var/www/html
WORKDIR /var/www/html

# Change ownership of our application
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 to the outside world
EXPOSE 80
