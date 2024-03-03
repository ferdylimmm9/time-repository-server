FROM php:8.2-fpm


RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


COPY . /var/www

WORKDIR /var/www


RUN composer install --no-interaction --no-ansi --no-scripts --no-progress --prefer-dist --ignore-platform-reqs


EXPOSE 8080


CMD ["php-fpm"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
