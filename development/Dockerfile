FROM php:7-fpm

RUN apt-get update && apt-get install -y \
        libicu-dev \
    && docker-php-ext-install \
        intl \
    && docker-php-ext-enable \
        intl

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli