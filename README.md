# CRUD de autos con Docker

## Primera ejecución

Desde esta carpeta:

```bash
sudo docker compose up -d --build --wait --wait-timeout 300
sudo docker compose ps
```

Abrir http://localhost:8080. Crear primero una marca y una categoría y después un auto.
El primer arranque descarga las imágenes y crea las tablas; puede tardar varios minutos.

Ya se generó un `.env` local con claves aleatorias, excluido del repositorio y de la imagen.
En una copia nueva, copiar `.env.example` a `.env` y sustituir ambos valores antes de arrancar.

## Qué hace cada archivo

- `Dockerfile`: construye la imagen con PHP 8.4, Apache, PDO MySQL y el código.
- `compose.yaml`: inicia la aplicación y MySQL 8.4 en una red común. La aplicación
  encuentra MySQL mediante el nombre `db`; localhost dentro del contenedor sería él mismo.
- `.env`: contiene las claves locales. El CRUD utiliza el usuario `autos`.
- `mysql_data`: volumen que conserva los datos al recrear el contenedor de MySQL.
- `database/concesionaria_mvc.sql`: crea las tablas únicamente al inicializar un volumen vacío.
- `public/health/live.php`: indica que PHP y Apache responden.
- `public/health/ready.php`: comprueba la conexión y la tabla de autos.

El puerto web se publica en 127.0.0.1:8080. MySQL queda accesible solo dentro de la red de Docker.
Compose espera a que MySQL pueda consultar la tabla antes de iniciar la aplicación.
Referencia: https://docs.docker.com/compose/how-tos/startup-order/

## Desarrollo

Para reflejar cambios del código sin reconstruir la imagen:

```bash
sudo docker compose -f compose.yaml -f compose.dev.yaml up -d --build --wait --wait-timeout 300
```

Para usar el código empaquetado en la imagen:

```bash
sudo docker compose up -d --build --wait --wait-timeout 300
```

## Diagnóstico y apagado

```bash
sudo docker compose ps
sudo docker compose logs --tail=80 app db
sudo docker compose down
```

`down` detiene y elimina los contenedores y la red, conservando el volumen.
Añadir `-v` elimina también la base de datos: no usarlo para una parada normal.
Cambiar las claves en `.env` después de inicializar MySQL no cambia automáticamente las claves guardadas en MySQL.

## Validación local del 23 de octubre de 2026

- Imagen `crud-autos:v1.0.0` construida y Compose arrancado correctamente.
- Aplicación y MySQL reportaron estado `healthy`.
- Endpoints de vida y disponibilidad respondieron HTTP 200.
- CRUD verificado por HTTP: crear marca, categoría y auto, consultar detalle,
  editar color y eliminar un segundo auto temporal.
- Se dejó un auto de prueba con placa `TEST-D5B4F57A`, color Verde, para verificar
  persistencia. La recreación de MySQL se completó: cambió el identificador del contenedor y
  el auto conservó sus datos y el color editado. Ambos servicios volvieron a `healthy`.

La etapa actual es local, con una instancia del CRUD. Antes de Kubernetes falta resolver
las sesiones compartidas entre réplicas, además del resto de
requisitos del proyecto.
