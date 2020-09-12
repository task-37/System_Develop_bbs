FROM php:7.2.0-fpm-alpine
RUN apk add -U --no-cache curl-dev
RUN docker-php-ext-install curl
Run docker-php-ext-install pdo_mysql
