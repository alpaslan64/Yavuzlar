FROM php:8.1-apache

RUN a2enmod rewrite

COPY ./src /var/www/html/

RUN docker-php-ext-install pdo pdo_mysql mysqli

WORKDIR /var/www/html/
