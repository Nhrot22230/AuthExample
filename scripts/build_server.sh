#!/bin/bash

# Directorio del proyecto
PROJECT_DIR="$HOME/AuthExample"

# Ir al directorio del proyecto
cd $PROJECT_DIR

# Actualizar el repositorio con 'git pull'
echo "Haciendo git pull..."
git pull origin main

# Salir del directorio del proyecto
cd -

# Eliminar todos los contenedores de Docker previos
echo "Eliminando contenedores Docker previos..."
sudo docker ps -aq | xargs sudo docker rm -f

# Detener y eliminar imágenes previas si es necesario (opcional)
# sudo docker rmi $(sudo docker images -q) -f

# Construir el contenedor Docker desde el Dockerfile
echo "Construyendo el contenedor Docker..."
sudo docker-compose build

# Levantar el contenedor nuevo
echo "Levantando el nuevo contenedor..."
sudo docker-compose up -d

echo "El servidor ha sido actualizado y está corriendo."
