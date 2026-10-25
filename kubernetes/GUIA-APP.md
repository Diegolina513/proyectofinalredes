# Aplicación con dos réplicas: preparación, todavía sin desplegar

MySQL ya está verificado: mysql-0 1/1, PVC Bound, servicio db y tablas disponibles.
Los archivos 05 a 08 solo están preparados localmente. El servidor los aceptó con
`kubectl apply --dry-run=server`: validación de prueba, sin persistir recursos.
No se transfirieron archivos a su disco ni se inició la aplicación.

| Archivo | Propósito |
| --- | --- |
| `05-php-sessions.yaml` | ConfigMap con PHP ini para sesiones por archivos compartidos. |
| `06-sessions-pvc.yaml` | PVC independiente `php-sessions`, local-path, solicitud 1Gi. |
| `07-app-deployment.yaml` | Deployment `app`, dos réplicas, imagen publicada v1.0.0. |
| `08-app-service.yaml` | Service `app`, ClusterIP interno, puerto 80, sin afinidad. |

## Primer paso: copiar solo los cuatro archivos nuevos

Ejecutar en la terminal LOCAL de Ubuntu, manteniendo abierta la terminal SSH:

```bash
scp -i "/home/diego/Downloads/redes2-acceso.pem" "/home/diego/Redes 2 proyecto/crud-autos/kubernetes"/0[5-8]-*.yaml ubuntu@54.90.155.82:/home/ubuntu/kubernetes/
```

El patrón `0[5-8]-*.yaml` selecciona solo archivos que empiezan por 05, 06, 07 u 08.
Este comando no aplica recursos, copia claves ni copia `.env`.

## Etapas posteriores: una por interacción

No ejecutar todos los pasos juntos. En la terminal SSH, aplicar primero el
ConfigMap PHP (05), luego el PVC de sesiones (06), después Deployment (07) y
Service (08). No regenerar `mysql-credentials` ni volver a inicializar MySQL.
Cada aplicación se hace con `sudo kubectl apply -f` y la ruta de un solo archivo
bajo `/home/ubuntu/kubernetes/`. Confirmar su salida antes de continuar.

Al terminar esas etapas, comprobar rollout y dos Pods listos; verificar configuración
y permisos PHP y luego comprobar una sesión entre réplicas y tras reemplazar un Pod.
Esas pruebas de ejecución todavía NO se han realizado. No basta con ver dos Pods
para afirmar que las sesiones compartidas funcionan.

## Cómo se conservan las sesiones

El código actual guarda mensajes de confirmación en `$_SESSION` antes de una
redirección HTTP. Si la siguiente petición llega a otra réplica, ambas necesitan
acceder a los mismos archivos de sesión.

El ConfigMap monta `/usr/local/etc/php/conf.d/zz-sessions.ini` sin modificar la imagen
ni reemplazar su configuración `app.ini`. Establece handler `files`, directorio
`/var/lib/php/sessions`, modo estricto y tiempo de limpieza de 1440 segundos.
Las dos réplicas montan ahí el MISMO PVC. PHP usa el bloqueo de archivos del
almacenamiento local. No se utiliza afinidad de sesión en el Service.

Cada Pod tiene un init container con la misma imagen de la app. Antes de iniciar
Apache, ajusta propietario de la raíz del volumen a `www-data:www-data` y permisos
0700. La imagen se basa en PHP Apache; el proceso maestro inicia como root y los
workers PHP/Apache atienden como www-data. No se ejecuta un chown recursivo ni se
borran sesiones existentes. Al recrear un Pod se vuelve a montar el PVC.

ReadWriteOnce permite montar el volumen desde varios Pods EN EL MISMO NODO.
No significa que permita un solo Pod. El PV local tiene afinidad al nodo: esta
solución se diseñó para el clúster de una máquina actual. No replica sesiones ante
pérdida de nodo/disco ni proporciona tolerancia a fallos de servidor. Al ampliar la
arquitectura a varios nodos debe revisarse el almacenamiento de sesiones.

El PVC es separado de `mysql-data`. Su solicitud `1Gi` no crea otro EBS, no reserva
un GiB físico y no establece una cuota real en local-path. Comparte el espacio
libre del disco de 30 GiB con el sistema, imágenes y demás cargas. No borrar este
PVC para probar la recuperación de un Pod; podrían perderse sus sesiones.

Cambios del ConfigMap montado con subPath no se propagan automáticamente a Pods
existentes: requieren un nuevo rollout. Cambiar la imagen implica actualizar
la referencia tanto del contenedor app como del init container.

## Configuración operativa

- Dos réplicas. Actualización gradual: maxSurge 1, maxUnavailable 0; puede existir
  una tercera réplica transitoria durante una actualización.
- DB_HOST=db, puerto3306, base concesionaria_mvc y usuario autos.
- DB_PASSWORD referencia MYSQL_PASSWORD del Secret existente mysql-credentials.
  No se incluyeron claves literales ni se leyó/cambió el Secret.
- Por réplica: solicitud100m CPU/128Mi, límite1 CPU/384Mi. Valores iniciales,
  no mediciones de rendimiento. Ajustar si se observan límites, OOM o presión real.
- Init: solicitud10m/16Mi, límite100m/64Mi; solo prepara permisos y finaliza.
- startup y liveness: HTTP /health/live.php (PHP/Apache).
- readiness: HTTP /health/ready.php (conexión a la base y consulta de autos).
  Una caída de MySQL retira la app del tráfico; no provoca reinicios por liveness.
- Sin token de ServiceAccount montado dentro de los Pods.
- Service app: ClusterIP80, targetPort http/80. No es una IP pública ni un Ingress.
  No esperar que http://54.90.155.82 muestre el CRUD todavía. Ingress/HTTPS viene después.

## Validación realizada

- YAML y OpenAPI oficial local. Se reconoció correctamente el tipo especial
  IntOrString que Kubernetes permite en maxSurge/maxUnavailable y puertos.
- Verificados selectores, dos réplicas, referencias de PVC/ConfigMap/Secret,
  coherencia de variables DB y rutas de las probes con el código existente.
- Configuración ini analizada y sintaxis sh del init comprobada sin ejecutarlo.
- API del K3s REAL aceptó 05 a 08 con dry-run=server (sin crear recursos).
- No se comprobó todavía arranque, permisos en volumen real, distribución de
  tráfico ni persistencia de sesión: se comprobarán después del despliegue manual.
- No se reconstruyó imagen, no hubo QA de navegador ni cambios de datos/infraestructura.

Referencias:
https://kubernetes.io/docs/concepts/storage/persistent-volumes/#access-modes
https://www.php.net/manual/en/session.configuration.php
https://kubernetes.io/docs/concepts/configuration/configmap/#mounted-configmaps-are-updated-automatically

## Preparación de actualización v1.0.1

El archivo07 se actualizó localmente a v1.0.1 en app e init. La imagen agrega
«Versión 1.0.1» al pie. Primero construir y publicar esa imagen, después copiar
07 y aplicarlo manualmente; aún no se realizó ninguno de esos pasos. La versión
previamente desplegada y verificada sigue siendov1.0.0.
