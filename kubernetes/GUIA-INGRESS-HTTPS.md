# Publicar el CRUD y habilitar HTTPS: preparado, sin aplicar

Estado remoto observado por lectura: app READY 2/2 AVAILABLE 2; Service app interno
80/TCP; IngressClass traefik; imagen de Traefik 3.7.13. Diego confirmó además Pods
app sin reinicios y ambos PVC Bound. No se realizó prueba funcional de sesiones
entre Pods en esta intervención.

DNS comprobado desde el equipo local: `54-90-155-82.sslip.io` -> `54.90.155.82`.
HTTP público devuelve 404 antes de aplicar el Ingress; confirma respuesta HTTP en
el destino, pero todavía no demuestra acceso al CRUD.

## Archivos

- `09-app-ingress-http.yaml`: Ingress app-http, host gratuito, Traefik web/80,
  ruta / Prefix al Service app puerto80. Este es el siguiente archivo a copiar.
- `10-letsencrypt-issuer.yaml`: Issuer namespaced redes2 para Let's Encrypt
  producción. Solver HTTP01, ingressClassName traefik, Service ClusterIP y
  entrada web. Cert-manager generará el Secret de cuenta; aquí solo hay su nombre.
- `11-app-certificate.yaml`: solicita certificado para el mismo host y lo guarda
  en Secret app-tls. Clave ECDSA256 con rotación. No contiene una clave privada.
- `12-app-ingress-https.yaml`: Ingress app-https separado, entrada websecure/443,
  TLS con Secret app-tls y backend app80.

Los Ingress HTTP y HTTPS tienen nombres distintos y pueden coexistir. Esta etapa
no configura redirección obligatoria HTTP->HTTPS; el puerto80 permanece disponible
para HTTP01. El certificado debe estar Ready antes de validar confianza HTTPS.
No dar por cumplido HTTPS solo porque el puerto443 responda con un certificado
predeterminado de Traefik. No omitir validación TLS ni aceptar advertencias.

## Único paso inmediato: copiar 09 desde terminal LOCAL

```bash
scp -i "/home/diego/Downloads/redes2-acceso.pem" "/home/diego/Redes 2 proyecto/crud-autos/kubernetes/09-app-ingress-http.yaml" ubuntu@54.90.155.82:/home/ubuntu/kubernetes/
```

No aplica recursos. No se transfiere .env ni clave privada. Mantener abierta SSH.
Luego, en una interacción separada, aplicar solo ese archivo desde la terminal SSH:

```bash
sudo kubectl apply -f /home/ubuntu/kubernetes/09-app-ingress-http.yaml
```

Tras confirmar su resultado, abrir http://54-90-155-82.sslip.io y comprobar el CRUD.
No ejecutar toda la guía de corrido.

## HTTPS: secuencia posterior, todavía no ejecutada

1. Instalar cert-manager desde el manifiesto oficial fijado en v1.21.3, versión
   publicada en su guía y de la rama1.21 compatible/probada con Kubernetes1.36:

```bash
sudo kubectl apply -f https://github.com/cert-manager/cert-manager/releases/download/v1.21.3/cert-manager.yaml
```

2. En otra etapa, comprobar sus tres componentes listos:

```bash
sudo kubectl -n cert-manager get pods
```

   Antes de aplicar Issuer/Certificate comprobar también disponibilidad del webhook;
   si el API aún no responde, esperar su arranque, no regenerar Secret/certificado.

3. Copiar posteriormente los archivos10-12 a /home/ubuntu/kubernetes (aún locales).
4. Aplicar solo10-letsencrypt-issuer.yaml; verificar Issuer Ready.
5. Aplicar solo11-app-certificate.yaml; cert-manager crea recursos temporales de
   desafío HTTP01 y Let's Encrypt comprueba control del host por puerto80.
6. Revisar Certificate app-tls y esperar Ready=True. Si no emite, revisar eventos,
   Order/Challenge, DNS y alcance80; no repetir emisiones a ciegas ni borrar claves.
7. Aplicar12-app-ingress-https.yaml después de la emisión. No toca Deployment ni PVC.
8. Verificar https://54-90-155-82.sslip.io con comprobación normal de certificado,
   host y cadena de confianza. La prueba de navegador queda para esa etapa.

Email ACME omitido de forma intencional: es opcional en el esquema oficial y
los ejemplos oficiales HTTP01 lo omiten. No se inventó ni extrajo un correo.
Se puede agregar después un contacto real si Diego lo desea, sin bloquear la emisión.
Cert-manager administrará claves y renovación; no exportar los Secret a Git o al chat.

## Alcance y dependencias

El DNS gratuito depende del servicio externo sslip.io; se verificó su resolución,
no se garantiza su disponibilidad permanente ni una emisión ACME todavía no probada.
El host incorpora la IP pública. Si EC2 cambia de IP al detener/arrancar, actualizar
host en09,11,12, transferir/aplicar esos cambios y emitir certificado para el host nuevo.
El nombre anterior apuntará a la IP anterior. No se compró dominio, se asignó Elastic
IP, se creó Load Balancer ni se cambió plan AWS. Componentes cert-manager usarán
recursos de la EC2 existente; no hay una nueva tarifa de infraestructura propuesta.

El clúster sigue siendo de un solo nodo. HTTPS no resuelve tolerancia a fallo de nodo.
No se modificaron05-08 ni las sesiones PHP en esta intervención.

## Validación

09 y12: YAML/OpenAPI oficial y dry-run=server aceptados por el API real sin crearlos.
10 y11: validados contra los esquemas CRD incluidos en el manifiesto oficial
cert-manager v1.21.3 descargado para lectura local; referencias y host coherentes.
Confirmado que email no es un campo requerido de ACME. Admisión por webhook y
emisión real pendientes hasta instalar cert-manager. No se aplicó ni transfirió nada.

Fuentes oficiales consultadas:
https://cert-manager.io/docs/releases/
https://cert-manager.io/docs/installation/kubectl/
https://cert-manager.io/docs/configuration/acme/http01/
https://cert-manager.io/docs/configuration/acme/
https://sslip.io/
