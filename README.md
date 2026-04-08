# PacificEcommerce Backend

Backend REST API de PacificEcommerce para gestion de programas, proyectos, usuarios y permisos, construido con Laravel 12 y arquitectura modular.

## Indice

1. [Descripcion del Proyecto](#descripcion-del-proyecto)
2. [Stack Tecnico Completo](#stack-tecnico-completo)
3. [Arquitectura y Estructura](#arquitectura-y-estructura)
4. [API y Autenticacion](#api-y-autenticacion)
5. [Setup Local](#setup-local)
6. [Variables de Entorno](#variables-de-entorno)
7. [Comandos Utiles](#comandos-utiles)
8. [Testing](#testing)
9. [Swagger OpenAPI](#swagger-openapi)
10. [Archivos Publicos y Uploads](#archivos-publicos-y-uploads)
11. [Checklist de Produccion](#checklist-de-produccion)
12. [Seguridad](#seguridad)

## Descripcion del Proyecto

Este backend expone una API versionada (`/api/v1`) con autenticacion JWT, control de acceso por scopes y documentacion OpenAPI.

Objetivos principales:

- Centralizar la logica de negocio en modulos por dominio.
- Mantener una estructura mantenible y escalable para nuevas entidades.
- Proveer contratos API estables para frontend y consumidores externos.

## Stack Tecnico Completo

### Core

- PHP `^8.2`
- Laravel Framework `^12.0`
- PostgreSQL (produccion)
- SQLite in-memory (testing)

### Seguridad y acceso

- JWT Auth: `php-open-source-saver/jwt-auth` (`^2.8`)
- Roles y permisos: `spatie/laravel-permission` (`^6.24`)
- Middleware de scopes por modulo (`scope:{modulo}` / `scope:{modulo}:write`)

### Documentacion API

- Swagger/OpenAPI: `darkaonline/l5-swagger` (`^9.0`)

### Mail y comunicaciones

- Verificacion de correo con Notifications de Laravel (`MustVerifyEmail`)
- Transporte de correo configurable por `MAIL_MAILER`
- Configuracion actual del proyecto: SMTP (Google)
- Paquete disponible para integracion Mailgun: `symfony/mailgun-mailer` (`^7.4`)

### Tooling de desarrollo

- Tinker: `laravel/tinker`
- Pint (code style): `laravel/pint`
- PHPUnit: `phpunit/phpunit` (`^11.5`)
- Mockery: `mockery/mockery`
- Collision: `nunomaduro/collision`
- Pail: `laravel/pail`
- Sail: `laravel/sail`

## Arquitectura y Estructura

Patron por modulo:

`Controller -> Service -> Repository -> Domain`

Estructura estandar:

`app/Modules/{Entidad}/Controller`
`app/Modules/{Entidad}/Service`
`app/Modules/{Entidad}/Repository`
`app/Modules/{Entidad}/Domain`

Convenciones clave:

- Tablas en singular.
- Rutas API versionadas en `/api/v1/*`.
- Validacion tecnica en FormRequest.
- Validacion de reglas de negocio en Domain/Service.
- Respuesta JSON estandarizada con `success`, `message` y `data`.

## API y Autenticacion

Base path principal:

- Privada autenticada: `/api/v1/*`
- Publica: `/api/v1/public/*`

Flujo de autenticacion JWT:

1. `POST /api/v1/auth/login`
2. Uso de token Bearer en requests autenticados
3. `POST /api/v1/auth/refresh` para renovar token
4. `GET /api/v1/auth/me` para obtener usuario autenticado

Control de acceso:

- Middleware `jwt` para proteger rutas.
- Middleware `scope:*` para permisos por modulo.
- Soporte de scope global `*:*` para perfiles admin.

## Setup Local

### Prerrequisitos

- PHP 8.2+
- Composer
- Node.js 18+ y npm
- PostgreSQL

### Instalacion

1. Instalar dependencias:

```bash
composer install
```

2. Configurar entorno:

```bash
cp .env-example .env
php artisan key:generate
php artisan jwt:secret
```

3. Base de datos (migraciones y seeders):

```bash
php artisan migrate --seed
```

4. Levantar entorno de desarrollo:

```bash
composer dev
```

Este comando levanta servidor Laravel, listener de queue y Vite en paralelo.

## Variables de Entorno

Revisar estas variables antes de ejecutar en cualquier ambiente.

### Aplicacion y CORS

- `APP_ENV`
- `APP_DEBUG` (en produccion debe ser `false`)
- `APP_URL`
- `FRONTEND_URL`

### Base de datos

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

### JWT

- `JWT_SECRET`
- `JWT_TTL`
- `JWT_REFRESH_TTL`
- `JWT_ALGO`
- `JWT_BLACKLIST_ENABLED`

### Correo

- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_ENCRYPTION`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

### reCAPTCHA v2 (registro)

- `RECAPTCHA_SITE_KEY`
- `RECAPTCHA_SECRET_KEY`
- `RECAPTCHA_EXPECTED_HOSTNAME` (opcional, recomendado en `staging/production`)

Notas reCAPTCHA:

- `RECAPTCHA_SITE_KEY` es publica para frontend.
- `RECAPTCHA_SECRET_KEY` es sensible y no debe exponerse.

## Comandos Utiles

```bash
# Desarrollo
composer dev

# Servidor solamente
php artisan serve

# Migraciones
php artisan migrate
php artisan migrate --seed

# Limpieza de cache
php artisan optimize:clear

# Ver rutas
php artisan route:list

# Formato de codigo
./vendor/bin/pint
```

## Testing

El proyecto usa PHPUnit y define suites `Unit` y `Feature`.

Entorno de pruebas:

- `APP_ENV=testing`
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=:memory:`

Comandos:

```bash
# Todos los tests
composer test

# Archivo especifico
php artisan test tests/Feature/AuthRegisterWithCountryTest.php

# Suite especifica
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## Swagger OpenAPI

Generacion de docs:

```bash
php artisan l5-swagger:generate
```

Rutas por defecto:

- UI: `/api/documentation`
- JSON: `/docs` (archivo generado en `storage/api-docs`)

## Archivos Publicos y Uploads

Para exponer `storage/app/public` en `public/storage`:

```bash
php artisan storage:link
```

## Checklist de Produccion

Antes de release:

1. `APP_ENV=production`
2. `APP_DEBUG=false`
3. Secretos cargados desde entorno seguro
4. Variables sensibles generadas de forma robusta
5. Migraciones aplicadas
6. `php artisan storage:link` ejecutado
7. Swagger regenerado
8. Verificacion de correo saliente

Comandos recomendados en deploy:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan l5-swagger:generate
```

## Seguridad

- No subir secretos al repositorio.
- Rotar secretos comprometidos antes de produccion.
- Usar credenciales distintas por ambiente (`local`, `staging`, `production`).
- Mantener `RECAPTCHA_SECRET_KEY` unicamente en variables de entorno seguras.
- Revisar periodicamente scopes JWT y permisos de roles.
