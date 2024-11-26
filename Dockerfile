# Usa una imagen oficial de PHP con FPM
FROM php:8.3-fpm

# Instala las dependencias del sistema y extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql pdo_sqlite zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./custom-php.ini /usr/local/etc/php/conf.d/

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos de la aplicación
COPY . .

# Copia el archivo .env o crea uno a partir del archivo de ejemplo
RUN cp .env.example .env

# Elimina el directorio vendor si existe y vuelve a instalar las dependencias con Composer
RUN rm -rf /var/www/html/vendor && composer install --no-dev --optimize-autoloader

# Ejecuta `composer install` para instalar las dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

# Genera la clave de la aplicación Laravel
RUN php artisan key:generate

# Exponer el puerto 8000 para la aplicación Laravel
EXPOSE 8000

CMD ["php", "artisan", "serve"]
