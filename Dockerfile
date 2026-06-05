FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y libxml2-dev \
    && docker-php-ext-install soap \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY public/ /var/www/html/

RUN a2enmod rewrite

EXPOSE 80