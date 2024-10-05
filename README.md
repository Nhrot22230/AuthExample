<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


# Installation Requirements

Para instalar y ejecutar un proyecto en Laravel, se requieren los siguientes componentes y extensiones.

## Composer

Laravel utiliza [Composer](https://getcomposer.org/) como su gestor de dependencias. Asegúrate de tener Composer instalado en tu sistema.

## PHP

Este proyecto requiere PHP versión **8.3**.

### Configuración de PHP

A continuación, se muestran los detalles de la configuración de PHP en tu sistema:

- **Configuration File (php.ini) Path**: `/etc/php/8.3/cli`
- **Loaded Configuration File**: `/etc/php/8.3/cli/php.ini`
- **Scan for additional .ini files in**: `/etc/php/8.3/cli/conf.d`
- **Additional .ini files parsed**:
  - `10-mysqlnd.ini`
  - `10-opcache.ini`
  - `10-pdo.ini`
  - `15-xml.ini`
  - `20-bz2.ini`
  - `20-calendar.ini`
  - `20-ctype.ini`
  - `20-curl.ini`
  - `20-dom.ini`
  - `20-exif.ini`
  - `20-ffi.ini`
  - `20-fileinfo.ini`
  - `20-ftp.ini`
  - `20-gettext.ini`
  - `20-iconv.ini`
  - `20-intl.ini`
  - `20-mbstring.ini`
  - `20-mysqli.ini`
  - `20-pdo_mysql.ini`
  - `20-phar.ini`
  - `20-posix.ini`
  - `20-raphf.ini`
  - `20-readline.ini`
  - `20-shmop.ini`
  - `20-simplexml.ini`
  - `20-sockets.ini`
  - `20-sysvmsg.ini`
  - `20-sysvsem.ini`
  - `20-sysvshm.ini`
  - `20-tokenizer.ini`
  - `20-xmlreader.ini`
  - `20-xmlwriter.ini`
  - `20-xsl.ini`
  - `20-zip.ini`
  - `25-http.ini`

## Módulos de PHP Requeridos

Asegúrate de que los siguientes módulos de PHP estén habilitados:

- `curl`
- `mbstring`
- `mysqlnd`
- `pdo`
- `pdo_mysql`
- `xml`
- `zip`

Puedes habilitar estos módulos editando tu archivo `php.ini` y asegurándote de que las siguientes líneas no estén comentadas:

```ini
extension=curl
extension=mbstring
extension=mysqlnd
extension=pdo
extension=pdo_mysql
extension=xml
extension=zip
```

Una vez que hayas configurado todo, puedes proceder a instalar las dependencias de tu proyecto Laravel ejecutando el siguiente comando en la raíz de tu proyecto:

```bash
composer update
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

Esta sección proporciona información clara y concisa sobre los requisitos de instalación para tu proyecto Laravel, asegurando que los usuarios tengan todo lo necesario para comenzar.

