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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql pdo_sqlite zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos de la aplicación
COPY . .

# Copia el archivo .env o crea uno a partir del archivo de ejemplo
RUN cp .env.example .env

# Ejecuta `composer install` para instalar las dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate

# Expone el puerto 8000
EXPOSE 8000

# Comando de inicio
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
