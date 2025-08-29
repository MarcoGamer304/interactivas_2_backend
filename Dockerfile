FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip git curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY --chown=www-data:www-data . .

RUN chmod -R 755 /var/www/storage

COPY ./nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 8080

CMD php-fpm -D && nginx -g 'daemon off;'
