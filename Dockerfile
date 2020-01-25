FROM php:7.4.2

LABEL maintainer="Aleksa Sukovic"

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN mkdir -p /var/www/scrapy

# Utilities
RUN apt-get update && apt-get install --no-install-recommends -y \
        wget \
        vim \
        git \
        unzip

# Composer + Extensions
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN pecl install xdebug-2.9.1 \
    && docker-php-ext-enable xdebug

