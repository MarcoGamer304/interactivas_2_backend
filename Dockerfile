FROM php:8.2-fpm

# Instalar nginx y dependencias
RUN apt-get update && apt-get install -y \
    nginx \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip git curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath gd

# Instalar composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar proyecto
COPY --chown=www-data:www-data . .

# Permisos de Laravel
RUN chmod -R 755 /var/www/storage

# Configuración de nginx
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Puerto Railway
EXPOSE 8080

# Arrancar php-fpm + nginx
CMD php-fpm -D && nginx -g 'daemon off;'
