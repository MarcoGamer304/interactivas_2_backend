FROM php:8.2-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim unzip git curl libonig-dev \
    libxml2-dev \
    libzip-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libpq-dev \
    nginx \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd

# Instalar composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar código
COPY --chown=www-data:www-data . .

# Permisos
RUN chmod -R 755 /var/www/storage

# Copiar configuración de Nginx
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Exponer el puerto que Railway usa
EXPOSE 8080

# Comando de arranque
CMD service php8.2-fpm start && nginx -g 'daemon off;'
