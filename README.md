# Installation Requirements

Para instalar y ejecutar un proyecto en Laravel, se requieren los siguientes componentes y extensiones.

## PHP

Este proyecto requiere PHP versión **8.3**.

### Instalación de PHP en Linux

Para instalar PHP 8.3 en sistemas basados en Linux (como Ubuntu), sigue los siguientes pasos:

```bash
add-apt-repository ppa:ondrej/php # Si apt no encuentra php8.3 por defecto
apt update
apt install php8.3 php8.3-zip php8.3-mysql php8.3-curl php8.3-cli php8.3-xml php8.3-mbstring
```

### Instalación de PHP en Windows

1. Descarga el instalador de PHP 8.3 desde el [sitio oficial de PHP para Windows](https://windows.php.net/download).
2. Extrae los archivos en una carpeta (por ejemplo, `C:\php`).
3. Añade la ruta de PHP a las variables de entorno:
   - Abre el "Panel de control" -> "Sistema" -> "Configuración avanzada del sistema" -> "Variables de entorno".
   - Edita la variable `Path` y agrega `C:\php`.
4. Instala las extensiones necesarias en el archivo `php.ini`:
   - Habilita las siguientes extensiones removiendo el `;` de cada línea en el archivo `php.ini`:

     ```ini
     extension=curl
     extension=mbstring
     extension=openssl
     extension=pdo_mysql
     extension=xml
     extension=zip
     ```

## Composer

Laravel utiliza [Composer](https://getcomposer.org/) como su gestor de dependencias. Asegúrate de tener Composer instalado en tu sistema.

### Instalación de Composer en Linux

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
```

### Instalación de Composer en Windows

1. Descarga el [instalador de Composer](https://getcomposer.org/Composer-Setup.exe).
2. Ejecuta el instalador y sigue las instrucciones en pantalla.
3. Verifica que Composer se haya instalado correctamente ejecutando el siguiente comando en la terminal:

```bash
composer --version
```

## Clonar el Repositorio

Clona el repositorio del proyecto:

```bash
git clone https://github.com/Nhrot22230/AuthExample
cd AuthExample
cp .env.example .env
```

Una vez dentro del directorio, instala las dependencias necesarias:

```bash
composer update
composer install
php artisan migrate --seed
php artisan serve

```
