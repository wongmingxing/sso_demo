FROM php:7.2-fpm

RUN apt-get update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install intl \
    && docker-php-ext-install pcntl \
    && rm -r /var/lib/apt/lists/*

COPY . /var/www/html

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/tmp