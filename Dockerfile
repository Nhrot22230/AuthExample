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

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos de la aplicación
COPY . .

# Copia el archivo .env o crea uno a partir del archivo de ejemplo
#RUN cp .env.example .env

# Elimina el directorio vendor si existe
RUN rm -rf /var/www/html/vendor && composer install --no-dev --optimize-autoloader

# Ejecuta `composer install` para instalar las dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader

#RUN php artisan key:generate

# Limpia cualquier caché previo de configuración
RUN php artisan config:clear

# Expone el puerto 80
EXPOSE 80

# Comando de inicio (corre los seeders solo si RUN_SEEDER=true)
#CMD if [ "$RUN_SEEDER" = "true" ]; then php artisan migrate --force --seed; fi && php artisan serve --host=0.0.0.0 --port=80
CMD php artisan migrate --force --seed && php artisan serve --host=0.0.0.0 --port=80
