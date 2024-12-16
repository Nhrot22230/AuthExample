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

    
# Descarga e instala el Amazon SSM Agent
RUN apt-get update && \
    apt-get install -y curl && \
    curl -o /tmp/amazon-ssm-agent.deb https://s3.amazonaws.com/amazon-ssm-us-east-1/latest/debian_amd64/amazon-ssm-agent.deb && \
    dpkg -i /tmp/amazon-ssm-agent.deb && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/amazon-ssm-agent.deb

RUN amazon-ssm-agent &

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Copia los archivos de la aplicación
COPY . .

# Copia el archivo .env o crea uno a partir del archivo de ejemplo
RUN cp .env.example .env

# Elimina el directorio vendor si existe
RUN rm -rf /var/www/html/vendor && composer install --no-dev --optimize-autoloader

# Ejecuta `composer install` para instalar las dependencias de Laravel
RUN composer install --optimize-autoloader

#RUN php artisan key:generate

# Limpia cualquier caché previo de configuración
RUN php artisan config:clear

# Expone el puerto 80
EXPOSE 80

# Comando de inicio
#CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
CMD php artisan migrate:fresh --force && php artisan db:seed && php artisan serve --host=0.0.0.0 --port=80