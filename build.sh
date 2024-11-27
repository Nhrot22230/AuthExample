#!/bin/bash

# Actualizar el sistema
echo "Actualizando el sistema..."
sudo apt-get update -y
sudo apt-get upgrade -y

# Instalar dependencias necesarias
echo "Instalando dependencias..."
sudo apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release \
    sudo

# Instalar Docker
echo "Instalando Docker..."
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Agregar usuario actual al grupo docker para evitar usar sudo con docker
echo "Agregando el usuario al grupo docker..."
sudo usermod -aG docker $USER

# Verificar instalación de Docker
echo "Verificando instalación de Docker..."
docker --version
if [ $? -eq 0 ]; then
    echo "Docker instalado correctamente."
else
    echo "Error al instalar Docker."
    exit 1
fi

# Instalar Docker Compose
echo "Instalando Docker Compose..."
DOCKER_COMPOSE_VERSION="1.29.2"
curl -L "https://github.com/docker/compose/releases/download/$DOCKER_COMPOSE_VERSION/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verificar instalación de Docker Compose
echo "Verificando instalación de Docker Compose..."
docker-compose --version
if [ $? -eq 0 ]; then
    echo "Docker Compose instalado correctamente."
else
    echo "Error al instalar Docker Compose."
    exit 1
fi

# Instalar Nginx
echo "Instalando Nginx..."
sudo apt-get install -y nginx

# Configurar Nginx para servir la aplicación
echo "Configurando Nginx..."
cat > /etc/nginx/sites-available/default <<EOF
server {
    listen 80;
    index index.php index.html;
    root /var/www/html/public;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Reiniciar Nginx para aplicar cambios
echo "Reiniciando Nginx..."
sudo systemctl restart nginx

# Crear directorio para los proyectos de Docker
echo "Creando directorio de proyecto Docker..."
mkdir -p /var/www/html

# Descargar un archivo `docker-compose.yml` básico
echo "Generando archivo docker-compose.yml..."
cat > /var/www/html/docker-compose.yml <<EOF
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    volumes:
      - .:/var/www/html
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
    command: php artisan serve --host=0.0.0.0 --port=8000

  nginx:
    image: nginx:latest
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - .:/var/www/html
    ports:
      - "80:80"
    depends_on:
      - app
EOF

# Crear archivo de configuración básico de Nginx
echo "Generando archivo nginx.conf..."
cat > /var/www/html/nginx.conf <<EOF
server {
    listen 80;
    index index.php index.html;
    root /var/www/html/public;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Levantar el contenedor con Docker Compose
echo "Levantando los contenedores con Docker Compose..."
cd /var/www/html
sudo docker-compose up -d

# Verificar que los contenedores estén corriendo
echo "Verificando los contenedores..."
sudo docker ps

# Mensaje de finalización
echo "¡Docker y la aplicación están configurados y corriendo!"
echo "Puedes acceder a la aplicación en http://localhost."

# Fin del script
exit 0
