FROM php:8.1-fpm

# Install required extensions and libraries
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    cron \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy application files to container
COPY . /var/www

WORKDIR /var/www

# Install application dependencies
RUN composer install --no-interaction --no-ansi --no-scripts --no-progress --prefer-dist --ignore-platform-req=ext-gd

RUN php artisan migrate --force && \
    php artisan db:seed && \
    php artisan optimize:clear

# Add cron job
RUN echo "* * * * * /usr/local/bin/php /var/www/artisan schedule:run >> /var/log/cron.log 2>&1" > /etc/cron.d/my-cron
RUN chmod 0644 /etc/cron.d/my-cron
RUN crontab /etc/cron.d/my-cron

EXPOSE 8080

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php-fpm"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
