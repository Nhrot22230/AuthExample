#!/bin/bash

# Verifica si Git está instalado
if ! command -v git &> /dev/null
then
    echo "Git no encontrado. Instalando Git..."
    # Actualiza el sistema e instala Git
    sudo apt-get update
    sudo apt-get install -y git
    echo "Git instalado."
else
    echo "Git ya está instalado."
fi
