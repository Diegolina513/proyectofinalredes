# Comprobaciones locales, paso a paso

Estas son verificaciones actuales del despliegue ya creado. No reconstruyen el
arranque anterior. Ejecutar una por vez y explicar la salida antes de seguir.
No se han obtenido capturas de pantalla: no hay una herramienta habilitada para
capturar la terminal del escritorio. Los archivos `.txt` son registros de terminal,
no imágenes. Para obtener imágenes reales, Diego puede usar la captura de pantalla
del sistema una vez que la salida esté visible y no aparezcan datos sensibles.

Trabajar desde:

```bash
cd "/home/diego/Redes 2 proyecto/crud-autos"
```

## 1. Contenedores: primera comprobación abierta en una terminal

```bash
sudo docker compose ps
```

Esperado: dos filas, servicios `app` y `db`, con estado `Up` y `healthy`.
`IMAGE` muestra `crud-autos:v1.0.0` para la app y `mysql:8.4` para la base.
`127.0.0.1:8080->80/tcp` significa que el navegador del equipo entra por el puerto
8080 y Docker lo conduce al puerto 80 de Apache dentro del contenedor.
`3306/tcp` sin una flecha hacia un puerto del equipo no publica MySQL al exterior.
Demuestra: contenedores presentes, estado actual y puerto web publicado.
`healthy` significa que pasó el comando de salud configurado; no comprueba por sí
solo todas las funciones del CRUD.
Captura esperada: comando y tabla completos, una vez finalizada la autenticación.
Registro textual real: `01-contenedores-terminal.txt`. Puede estar pendiente de sudo.

## 2. Configuración de la aplicación: preparada, todavía no ejecutada en esta secuencia

```bash
cat Dockerfile
```

Esperado: imagen base `php:8.4-apache`, instalación de `pdo_mysql`, configuración
del directorio público y copia del código. Demuestra cómo se construye la imagen;
por sí solo no prueba que la construcción termine correctamente.
Captura esperada: el comando y el contenido del Dockerfile, sin mostrar `.env`.

## 3. Salud HTTP: preparada, todavía no ejecutada en esta secuencia

```bash
curl -i http://localhost:8080/health/live.php
curl -i http://localhost:8080/health/ready.php
```

Ejecutar primero uno y explicar, luego el otro. Esperado: HTTP 200 con `alive`
y HTTP 200 con `ready`. El primero comprueba respuesta de Apache/PHP. El segundo
comprueba que PHP puede conectar con MySQL y consultar la tabla `autos`.
Captura esperada: cada comando y su respuesta HTTP real.

## 4. Volumen: preparada, todavía no ejecutada en esta secuencia

```bash
sudo docker inspect redes2-autos-db-1 --format '{{range .Mounts}}{{println .Type .Name .Destination}}{{end}}'
```

Esperado: una entrada de tipo `volume` con nombre `redes2-autos_mysql_data` y destino
`/var/lib/mysql`. También aparece el montaje de tipo `bind` del SQL inicial.
Demuestra que el contenedor actual tiene montado ese volumen para sus datos.
No demuestra por sí solo que sobrevivan a una recreación: esa prueba se ejecutó
antes y quedó registrada en `../persistencia.txt`, sin captura original.
Captura esperada: comando y montajes, sin variables de entorno ni credenciales.

No volver a recrear contenedores solamente para conseguir una imagen.
