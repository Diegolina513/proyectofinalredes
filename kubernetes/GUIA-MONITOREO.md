# Monitoreo y SNMP: pasos pendientes de ejecutar

El agente snmpd fue instalado por Diego. Comprobación SSH de lectura: servicio
active y escucha solo127.0.0.1/::1:161 con configuración inicial; grupoDebian-snmp
existe. CIDR real de Pods10.42.0.0/24, IP privada de nodo172.31.21.155.
No se modificó, reinició ni configuró snmpd mediante herramientas del asistente.

## Componentes

-13-kube-state-metrics.yaml: métricas reales de Pods/Deployment/PVC en redes2.
Role de solo list/watch, sin acceso a Secret. Imagen oficialv2.20.0, basada en
client-go0.36.3 para la ramaKubernetes1.36.
-14-snmp-exporter.yaml: consulta SNMP real al agente del host y presenta métricas
para Prometheus. Imagen oficialv0.30.1. Requiere Secret snmp-config aún no creado.
-15-prometheus.yaml: Prometheusv3.13.5, gráficas y alertas visibles en UI; historial
24h/tamaño máximoTSDB1GB con PVC local-path solicitado2Gi. No es cuota efectiva
ni otro discoAWS. Recursos iniciales limitados: Prom512Mi, KSM128Mi, SNMP96Mi.
-16-snmpd.conf.template: agente limitado al sistemaSNMP y CIDR de Pods.
-17-snmp-exporter.yml.template: móduloGET real sysUpTime. Contiene marcador de
credencial, no un community real. No aplicar plantillas como manifiestosKubernetes.

Todos los servicios son internosClusterIP. No hay Ingress ni puerto público del
monitoreo. Prometheus ofrece su propia interfaz de tablas/gráficas; no hace falta
Grafana para este montaje. Las alertas son visibles en la UI; no se configuró envío
de notificaciones externas ni Alertmanager.

## Una etapa por interacción

No ejecutar la guía completa de corrido. Usuario ejecuta en terminal normal y
revisa resultado antes de avanzar. SSH significa servidor; LOCAL significa PC.

### 1. Copia de respaldo de configuración actual (SSH)

```bash
sudo cp -an /etc/snmp/snmpd.conf /etc/snmp/snmpd.conf.pre-redes2
```

No sobrescribe respaldo previo y no cambia configuración activa. Es el próximo
paso sugerido; todavía no confirmado por Diego.

### 2. Copiar nuevos archivos (LOCAL)

```bash
scp -i "/home/diego/Downloads/redes2-acceso.pem" "/home/diego/Redes 2 proyecto/crud-autos/kubernetes"/1[3-7]-* ubuntu@54.90.155.82:/home/ubuntu/kubernetes/
```

Solo copia13-17. No transfiere claves ni .env ni aplica recursos.

### 3. Generar credencial nueva protegida (SSH)

```bash
sudo sh -c 'umask 077; set -C; openssl rand -hex 32 > /etc/snmp/redes2-community'
```

Genera community aleatoria en archivo root0600, sin imprimir su valor. No reutiliza
public ni clavesMySQL. Si archivo ya existe, detiene la redirección: NO borrar ni
regenerar ciegamente. Nunca mostrar ese archivo ni incluirlo en capturas/Git.

### 4. Configurar agente desde plantilla y credencial (SSH)

```bash
sudo python3 -c 'from pathlib import Path; import os,grp,re; s=Path("/etc/snmp/redes2-community").read_text().strip(); assert re.fullmatch("[0-9a-f]{64}",s), "Credencial invalida"; p=Path("/etc/snmp/snmpd.conf"); p.write_text(Path("/home/ubuntu/kubernetes/16-snmpd.conf.template").read_text().replace("__SNMP_COMMUNITY__",s)); os.chown(p,0,grp.getgrnam("Debian-snmp").gr_gid); os.chmod(p,0o640)'
```

Solo genera la configuración protegida: no reinicia el agente ni imprime la clave.
El agente escuchará IPprivada172.31.21.155:161 y aceptará solicitudes de10.42.0.0/24
para lectura de subtree .1.3.6.1.2.1.1. No abrir UDP161 enAWS.
SNMPv2c NO cifra el community en tránsito; se limita al trayecto local Pods-host.
No presentarlo como SNMPv3 o transporte cifrado. Si cambia topología/CIDR, revisar
estas restricciones antes de continuar.

### 5. Reiniciar agente (SSH)

```bash
sudo systemctl restart snmpd
```

### 6. Comprobar escucha (SSH)

```bash
sudo ss -lunp 'sport = :161'
```

Esperado172.31.21.155:161, no0.0.0.0 niIPpública. Si restart falla, revisar estado
sin imprimir la configuración que contiene la credencial.

### 7. Crear Secret de exporter sin exponer community (SSH)

```bash
sudo python3 -c 'from pathlib import Path; import json,re; s=Path("/etc/snmp/redes2-community").read_text().strip(); assert re.fullmatch("[0-9a-f]{64}",s), "Credencial invalida"; config=Path("/home/ubuntu/kubernetes/17-snmp-exporter.yml.template").read_text().replace("__SNMP_COMMUNITY__",s); print(json.dumps({"apiVersion":"v1","kind":"Secret","metadata":{"name":"snmp-config","namespace":"redes2"},"type":"Opaque","stringData":{"snmp.yml":config}}))' | sudo kubectl create -f -
```

Ejecutar el comando completo: la salidaPython va directamente a kubectl, no a la
pantalla. Esperado secret/snmp-config created. Si existe, detenerse y revisar sin
rotarlo. No ejecutar primera parte por separado ni exportar Secret comoYAML/JSON.

### 8 a10. Aplicar cada archivo en etapa separada (SSH)

```bash
sudo kubectl apply -f /home/ubuntu/kubernetes/13-kube-state-metrics.yaml
```

```bash
sudo kubectl apply -f /home/ubuntu/kubernetes/14-snmp-exporter.yaml
```

```bash
sudo kubectl apply -f /home/ubuntu/kubernetes/15-prometheus.yaml
```

Luego observar Pods y PVC, esperar disponibilidad y comprobar logs si hay errores.
Las probes de snmp-exporter verifican que el exporter responde, no que SNMP llegue
al host: esa segunda comprobación se acredita con el targetsnmpUP y sysUpTime real.

## Acceder a gráficas por túnel, después del despliegue

Desde una terminalLOCAL nueva abrir una sesiónSSH con túnel:

```bash
ssh -i "/home/diego/Downloads/redes2-acceso.pem" -L 9090:127.0.0.1:9090 ubuntu@54.90.155.82
```

En ESA sesiónSSH ejecutar:

```bash
sudo kubectl -n redes2 port-forward --address=127.0.0.1 service/prometheus 9090:9090
```

Mantenerla abierta y visitar http://localhost:9090 en la PC. Ctrl+C cierra el
port-forward, no borra Prometheus. No publicar este panel sin autenticación.

En Status/Targets comprobar jobs prometheus, kube-state-metrics, snmp-exporter y
snmpUP. En Query consultar una expresión por vez:

- `redes2_app_replicas_disponibles`: debe reflejar réplicas disponibles, normalmente2.
- `redes2_app_replicas_deseadas`: réplicas solicitadas,2.
- `kube_pod_status_ready{namespace="redes2",condition="true"}`: estado real porPod.
- `kube_pod_container_info{namespace="redes2",container="app"}`: imagen de cada réplica.
- `up{job="snmp"}`:1 si consultaSNMP termina correctamente.
- `redes2_snmp_agente_activo_segundos`: sysUpTime real/100. Es tiempo desde inicio
  del agenteSNMP, NO tiempo desde arranque del sistema operativo.

Ver sysUpTime con valor real, además de UP, acredita comunicación SNMP; las métricas
Kubernetes no la sustituyen. Reiniciar snmpd reinicia ese contador.

## Evidencias y límites

No se borró ningúnPod ni se provocaron fallos en esta preparación. Las pruebas de
recuperación de réplica, actualización y persistenciaMySQL se harán después con
monitoreo ya funcionando. Las series a5s pueden no capturar transiciones muy
breves: observar también eventos y cambios de identidadPod; no simular métricas
ni declarar una caída que no haya quedado registrada. El monitoreo del mismo nodo
no estará disponible si cae laEC2. PVC local no replica métricas en otros nodos.

Validación de preparación: YAML/OpenAPI y dry-runAPI verifican recursosKubernetes;
configPrometheus y reglas analizadas comoYAML, sin promtool todavía. PlantillaSNMP
analizada comoYAML y cotejada con estructura oficial; falta prueba del binario y
consultaSNMP real. Ningún componente de monitoreo ha sido instalado o desplegado.

Fuentes:
https://prometheus.io/docs/prometheus/latest/configuration/configuration/
https://github.com/kubernetes/kube-state-metrics/releases/tag/v2.20.0
https://github.com/prometheus/snmp_exporter/releases/tag/v0.30.1
https://github.com/prometheus/snmp_exporter
