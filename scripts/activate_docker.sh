#!/bin/bash

# Verifica si Docker ya está instalado
if ! command -v docker &> /dev/null
then
    echo "Docker no encontrado. Instalando Docker..."
    # Actualiza el sistema y instala dependencias
    sudo apt-get update
    sudo apt-get install -y apt-transport-https ca-certificates curl software-properties-common

    # Agrega la clave GPG oficial de Docker
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -

    # Agrega el repositorio de Docker
    sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"

    # Actualiza los paquetes de APT
    sudo apt-get update

    # Instala Docker
    sudo apt-get install -y docker-ce

    # Habilita Docker para que se inicie automáticamente
    sudo systemctl enable docker
    sudo systemctl start docker

    echo "Docker instalado y en ejecución"
else
    echo "Docker ya está instalado."
fi
