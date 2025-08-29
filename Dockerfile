FROM php:8.2-fpm

# Instalar nginx y dependencias para Laravel
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip git curl \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Instalar composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www

# Copiar código del proyecto
COPY --chown=www-data:www-data . .

# Permisos
RUN chmod -R 755 /var/www/storage

# Copiar configuración de nginx
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Exponer puerto
EXPOSE 8080

# Arrancar php-fpm y nginx
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"
