#!/bin/bash

# Verificar si el script se está ejecutando como superusuario
if [ "$EUID" -ne 0 ]; then
    echo "Por favor ejecuta el script como superusuario (root)."
    exit 1
fi

# Actualizar paquetes e instalar Nginx
echo "Actualizando los paquetes e instalando Nginx..."
apt-get update -y
apt-get install -y nginx

# Configurar Nginx
echo "Configurando Nginx para redirigir HTTP 80 -> HTTP Docker 8000..."
NGINX_CONF="/etc/nginx/sites-available/docker_redirect"

cat > $NGINX_CONF <<EOF
server {
    listen 80;

    location / {
        proxy_pass http://localhost:8000; # Redirige al contenedor Docker en el puerto 8000
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    }
}
EOF

# Enlazar el archivo de configuración en sites-enabled
echo "Activando la configuración de Nginx..."
ln -sf /etc/nginx/sites-available/docker_redirect /etc/nginx/sites-enabled/

# Verificar la configuración de Nginx
echo "Verificando la configuración de Nginx..."
nginx -t

if [ $? -eq 0 ]; then
    # Reiniciar Nginx para aplicar los cambios
    echo "Reiniciando Nginx..."
    systemctl restart nginx

    echo "La configuración de Nginx se aplicó correctamente. Nginx ahora redirige HTTP 80 -> HTTP Docker 8000."
else
    echo "Hubo un error en la configuración de Nginx. Revisa los logs e inténtalo nuevamente."
    exit 1
fi

# Fin del script
echo "¡Script completado con éxito!"
