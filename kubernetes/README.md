# MySQL en Kubernetes: etapa aplicada y verificada

Los archivos 01 a 04 fueron copiados y aplicados por Diego. MySQL se verificó
remotamente: mysql-0 1/1 Running, PVC Bound y acceso a las tres tablas como usuario autos.
Los pasos de MySQL siguientes quedan como referencia del procedimiento realizado;
no repetir la creación del Secret ni reinicializar la base.

La siguiente etapa de aplicación se documenta en [GUIA-APP.md](GUIA-APP.md):
archivos 05 a 08 preparados y validados, todavía sin desplegar.

## Archivos y propósito

| Archivo | Función |
| --- | --- |
| `01-mysql-schema.yaml` | ConfigMap con el esquema real de `database/concesionaria_mvc.sql`. |
| `02-mysql-pvc.yaml` | PVC `mysql-data`, solicitud 5 GiB con `local-path`. |
| `03-mysql-service.yaml` | Servicio interno headless `db`, puerto 3306. |
| `04-mysql-statefulset.yaml` | Una instancia estable `mysql-0`, imagen `mysql:8.4`, volumen y probes. |

La app futura usará `DB_HOST=db`, `DB_PORT=3306`, `DB_NAME=concesionaria_mvc`
y `DB_USER=autos`, en el mismo namespace `redes2`. `DB_PASSWORD` deberá tomar
la clave `MYSQL_PASSWORD` del Secret `mysql-credentials`. La aplicación no usará root.
Los manifiestos de app se prepararon después, en los archivos 05 a 08; no se cambió la imagen.

## Transferencia de MySQL ya realizada (referencia)

Mantener abierta la terminal SSH. En una segunda terminal del equipo Ubuntu local:

```bash
scp -i "/home/diego/Downloads/redes2-acceso.pem" -r "/home/diego/Redes 2 proyecto/crud-autos/kubernetes" ubuntu@54.90.155.82:/home/ubuntu/
```

Copia solo esta carpeta de manifiestos y guía a `/home/ubuntu/kubernetes`.
No transfiere el `.env`, la clave privada ni el código completo. La clave se usa
localmente para autenticar la conexión. La IP es la confirmada por Diego para
esta sesión y puede cambiar después de detener y arrancar EC2.

## Etapas de MySQL ya realizadas (referencia)

No ejecutar toda esta guía de corrido. Los comandos siguientes son para cuando
se explique cada paso y Diego confirme la salida anterior.

### ConfigMap del esquema

```bash
sudo kubectl apply -f /home/ubuntu/kubernetes/01-mysql-schema.yaml
```

Esperado: `configmap/mysql-schema created`. Contiene las tablas marcas,
categorias y autos, sin contraseñas ni datos INSERT.

### Solicitar almacenamiento

```bash
sudo kubectl apply -f /home/ubuntu/kubernetes/02-mysql-pvc.yaml
```

Esperado: `persistentvolumeclaim/mysql-data created`. El PVC puede quedar
`Pending` hasta que exista un Pod que lo necesite (`WaitForFirstConsumer`).

**No crear otro volumen desde la consola AWS.** `local-path` crea un directorio
sobre el disco existente de la instancia (30 GiB configurados), normalmente bajo
`/var/lib/rancher/k3s/storage`. Los 5 GiB del YAML no son una partición, una reserva
física ni una cuota efectiva. El provisioner no impone límites de capacidad.
La capacidad real depende del espacio libre compartido con Ubuntu, imágenes,
K3s y el resto de los servicios. Revisar `df -h /` y vigilar uso de disco al desplegar.
Este PVC soporta recreación del Pod en el mismo nodo; no replica datos ni permite
recuperación automática frente a pérdida de la instancia o de su disco.
La política de reclaim depende de la StorageClass; no borrar el PVC para probar persistencia.

Fuente: https://github.com/rancher/local-path-provisioner#cons

### Servicio interno

```bash
sudo kubectl apply -f /home/ubuntu/kubernetes/03-mysql-service.yaml
```

Esperado: `service/db created`. Es headless (`clusterIP: None`): el nombre `db`
resuelve las direcciones de sus Pods listos. No publica MySQL en la IP de EC2.

### Generar las credenciales dentro del servidor

Hacer esto una sola vez, antes de iniciar MySQL, en la terminal SSH. El comando
completo envía dos claves aleatorias distintas directamente a Kubernetes mediante
una tubería; no imprime sus valores, no los escribe en archivos ni los incluye
en los argumentos de kubectl. No ejecutar la parte Python por separado.

```bash
python3 -c 'import json,secrets; print(json.dumps({"apiVersion":"v1","kind":"Secret","metadata":{"name":"mysql-credentials","namespace":"redes2"},"type":"Opaque","stringData":{"MYSQL_ROOT_PASSWORD":secrets.token_hex(32),"MYSQL_PASSWORD":secrets.token_hex(32)}}))' | sudo kubectl create -f -
```

Esperado: `secret/mysql-credentials created`. Solo capturar esa confirmación.
Si dice `AlreadyExists`, detenerse y conservar el Secret; no regenerarlo.
No exportar el Secret como YAML/JSON, no ponerlo en Git ni mostrar sus valores.
Un Secret debe protegerse también con permisos de acceso al clúster; el nombre
Secret no significa que sus valores estén necesariamente cifrados en reposo.
Cambiar el Secret después de inicializar MySQL NO actualiza las contraseñas de
usuarios ya creados dentro de la base de datos.

### Iniciar MySQL

```bash
sudo kubectl apply -f /home/ubuntu/kubernetes/04-mysql-statefulset.yaml
```

Esperado: `statefulset.apps/mysql created`. Usa una sola réplica; no escalar este
StatefulSet porque todas apuntarían al mismo PVC. El SQL de inicialización solo
se ejecuta automáticamente cuando el directorio de datos está vacío. La base
cloud inicia vacía: este paso no copia los registros de Docker Compose local.

Recursos iniciales: CPU solicitada 250m y límite 1 CPU; memoria solicitada
512 MiB y límite 1536 MiB. Ajustar según consumo real; estos valores no son una
medición ni garantizan rendimiento.

- `startupProbe`: espera hasta aproximadamente 10 minutos a que MySQL escuche
  TCP. Mientras tanto Kubernetes no ejecuta liveness/readiness.
- `livenessProbe`: `mysqladmin ping` comprueba que el servidor responde. Este
  comando puede considerar vivo al servidor incluso si rechaza autenticación;
  no se usa para validar credenciales ni tablas.
- `readinessProbe`: conecta por TCP como usuario `autos` y consulta las tres
  tablas. Así MySQL solo aparece listo para la app si credenciales y esquema funcionan.
- El Pod tiene hasta 60 segundos para finalizar antes de una terminación forzada.

### Observar el arranque

```bash
sudo kubectl -n redes2 get pods,pvc,svc
```

Esperado finalmente: `mysql-0` en `Running` con `1/1`, PVC `mysql-data` en
`Bound` con `5Gi`, Service `db` con `CLUSTER-IP None`. `5Gi` reportado no demuestra
una cuota efectiva. Si no queda listo, revisar eventos y logs en una etapa separada.
No hay que recrear el Pod ni borrar PVC/volúmenes en este paso.

## Validación local y límites

Los cuatro YAML se validaron sintácticamente y contra el esquema OpenAPI publicado
por el repositorio oficial Kubernetes (rama master, no el API de este servidor).
Se comprobó la igualdad del SQL normalizado con el archivo original, las referencias
entre Service/StatefulSet/PVC/ConfigMap y la sintaxis de las sondas.
No hay valores de contraseñas en los manifiestos; solo referencias al Secret futuro.
Posteriormente se verificó la ejecución real remota de MySQL: Pod listo, PVC Bound,
rollout completado y consultas por TCP a las tres tablas. Véase
`../evidencias/2026-10-23/mysql-kubernetes-verificado.txt`. La prueba de persistencia
Kubernetes tras recrear el Pod sigue pendiente.

Referencias:
- https://hub.docker.com/_/mysql
- https://kubernetes.io/docs/concepts/workloads/controllers/statefulset/
- https://kubernetes.io/docs/tasks/configure-pod-container/configure-liveness-readiness-startup-probes/

## Avance app e Ingress

Diego aplicó05-08. Se verificó remotamente Deployment app READY2/2 AVAILABLE2
y Service app80. La prueba funcional de sesiones entre réplicas sigue pendiente.
La siguiente etapa está en [GUIA-INGRESS-HTTPS.md](GUIA-INGRESS-HTTPS.md).
Archivos09-12 preparados y validados; todavía sin aplicar ni transferir.
