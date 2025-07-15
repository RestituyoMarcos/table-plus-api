<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Table Plus - Backend API

Esta es la API de backend para la aplicación de gestión de tareas "Table Plus", desarrollada con Laravel.

## Acerca del Proyecto

Este proyecto proporciona todos los endpoints necesarios para la gestión de tareas, autenticación de usuarios, notificaciones, backups y más. Está diseñado para ser consumido por un cliente frontend.

### Desarrollado con

* [Laravel](https://laravel.com/)
* [PHP](https://www.php.net/)
* [Laravel Sanctum](https://laravel.com/docs/sanctum) (para autenticación de API)
* [Swagger (L5-Swagger)](https://github.com/DarkaOnLine/L5-Swagger) (para documentación de API)

---

## Cómo Empezar

Sigue estos pasos para configurar y ejecutar el proyecto en tu entorno local.

### Prerrequisitos

Asegúrate de tener instalado PHP, Composer y un gestor de base de datos (como MySQL) en tu sistema.
* **Composer**
    ```sh
    # Asegúrate de que composer esté instalado y en tu PATH
    ```

### Instalación

1.  **Clona el repositorio**
    ```sh
    git clone [https://github.com/RestituyoMarcos/table-plus-api.git](https://github.com/RestituyoMarcos/table-plus-api.git)
    ```
2.  **Navega al directorio del proyecto**
    ```sh
    cd table-plus-api
    ```
3.  **Instala las dependencias de Composer**
    ```sh
    composer install
    ```
4.  **Crea el archivo de entorno**

    Copia el archivo de ejemplo `.env.example` para crear tu propio archivo de configuración.
    ```sh
    cp .env.example .env
    ```
5.  **Genera la clave de la aplicación**
    ```sh
    php artisan key:generate
    ```
6.  **Configura tu archivo `.env`**

    Abre el archivo `.env` y configura las variables de la base de datos y del servicio de correo.
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=table_plus
    DB_USERNAME=root
    DB_PASSWORD=tu_contraseña

    MAIL_MAILER=smtp
    MAIL_HOST=smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=tu_usuario_de_mailtrap
    MAIL_PASSWORD=tu_password_de_mailtrap
    ```
7.  **Ejecuta las migraciones y los seeders**

    Esto creará las tablas de la base de datos y las poblará con datos de ejemplo.
    ```sh
    php artisan migrate --seed
    ```
8.  **Crea el enlace simbólico para el almacenamiento**

    Para que los archivos subidos sean públicamente accesibles.
    ```sh
    php artisan storage:link
    ```

### Uso

Para iniciar el servidor de desarrollo, ejecuta el siguiente comando:
```sh
php artisan serve
```
La API estará disponible en http://127.0.0.1:8000.

Claro, aquí tienes la plantilla completa para el archivo README.md del proyecto de backend en Laravel, en formato de código.

Markdown

# Table Plus - Backend API

Esta es la API de backend para la aplicación de gestión de tareas "Table Plus", desarrollada con Laravel.

## Acerca del Proyecto

Este proyecto proporciona todos los endpoints necesarios para la gestión de tareas, autenticación de usuarios, notificaciones, backups y más. Está diseñado para ser consumido por un cliente frontend (como una aplicación React).

### Desarrollado con

* [Laravel](https://laravel.com/)
* [PHP](https://www.php.net/)
* [Laravel Sanctum](https://laravel.com/docs/sanctum) (para autenticación de API)
* [Swagger (L5-Swagger)](https://github.com/DarkaOnLine/L5-Swagger) (para documentación de API)

---

## Cómo Empezar

Sigue estos pasos para configurar y ejecutar el proyecto en tu entorno local.

### Prerrequisitos

Asegúrate de tener instalado PHP, Composer y un gestor de base de datos (como MySQL) en tu sistema.
* **Composer**
    ```sh
    # Asegúrate de que composer esté instalado y en tu PATH
    ```

### Instalación

1.  **Clona el repositorio**
    ```sh
    git clone [https://github.com/tu-usuario/table-plus-api.git](https://github.com/tu-usuario/table-plus-api.git)
    ```
2.  **Navega al directorio del proyecto**
    ```sh
    cd table-plus-api
    ```
3.  **Instala las dependencias de Composer**
    ```sh
    composer install
    ```
4.  **Crea el archivo de entorno**

    Copia el archivo de ejemplo `.env.example` para crear tu propio archivo de configuración.
    ```sh
    cp .env.example .env
    ```
5.  **Genera la clave de la aplicación**
    ```sh
    php artisan key:generate
    ```
6.  **Configura tu archivo `.env`**

    Abre el archivo `.env` y configura las variables de la base de datos y del servicio de correo.
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=table_plus_db
    DB_USERNAME=root
    DB_PASSWORD=tu_contraseña

    MAIL_MAILER=smtp
    MAIL_HOST=smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=tu_usuario_de_mailtrap
    MAIL_PASSWORD=tu_password_de_mailtrap
    ```
7.  **Ejecuta las migraciones y los seeders**

    Esto creará las tablas de la base de datos y las poblará con datos de ejemplo.
    ```sh
    php artisan migrate --seed
    ```
8.  **Crea el enlace simbólico para el almacenamiento**

    Para que los archivos subidos sean públicamente accesibles.
    ```sh
    php artisan storage:link
    ```

### Uso

Para iniciar el servidor de desarrollo, ejecuta el siguiente comando:
```sh
php artisan serve
```

La API estará disponible en http://127.0.0.1:8000.

## Documentación de la API
La API está documentada con Swagger. Para generar la documentación, ejecuta:

```sh
php artisan l5-swagger:generate
```
Puedes acceder a la documentación en la siguiente ruta de tu aplicación:
/api/documentation

### Tareas Programadas (Cron Job)
Este proyecto utiliza el planificador de Laravel para enviar notificaciones. Para que funcione en un servidor de producción, debes añadir el siguiente cron job a tu servidor.

```sh
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

Si esta en windows
Usar el Programador de Tareas de Windows

#### Pasos:
1. **Abrir el Programador de Tareas:**

Presiona la tecla de Windows, escribe "Programador de Tareas" (Task Scheduler) y ábrelo.

2. **Crear una Tarea Básica:**

En el panel derecho, haz clic en "Crear tarea básica...".

Dale un nombre a la tarea, como "Laravel Cron Job".

3. **Configurar el Desencadenador (Trigger):**

Elige la frecuencia con la que quieres que se ejecute el comando (por ejemplo, "Diariamente" o "Al iniciar el equipo").

**Para simular una ejecución "cada minuto", después de crear la tarea, puedes editar sus propiedades y en la pestaña "Desencadenadores", configurar que se repita cada 5 minutos (que es el intervalo más frecuente que permite la interfaz básica).**

4. **Configurar la Acción:**

Selecciona "Iniciar un programa".

En el campo "Programa/script", escribe php.

En el campo "Agregar argumentos (opcional)", pega artisan schedule:run.

En el campo "Iniciar en (opcional)", pega la ruta a tu proyecto: C:\Users\ruta-aproyecto\table-plus-api.

5. **Finalizar:**

Revisa la configuración y haz clic en "Finalizar".

La tarea ahora estará programada y se ejecutará automáticamente según la frecuencia que estableciste, ejecutando el planificador de Laravel correctamente en tu entorno de Windows.






