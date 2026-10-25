# Pruebas de la práctica — 24 de octubre de 2026

Procedencia: comandos, salidas y comprobaciones aportados por Diego durante
la práctica manual. Las capturas originales las conserva Diego.

| Prueba | Resultado observado |
| --- | --- |
| Prometheus Targets | prometheus, kube-state-metrics, snmp-exporter y snmp UP. |
| SNMP real | redes2_snmp_agente_activo_segundos devolvió 549.18; consulta al agente real. |
| Réplicas disponibles | redes2_app_replicas_disponibles devolvió 2. |
| Recuperación de un Pod app | Se eliminó app-5dfd9864d5-fbm97 y apareció app-5dfd9864d5-pbrqt listo; la otra réplica xkhsn permaneció. Métrica volvió a 2. |
| Persistencia MySQL | Se creó un auto, se eliminó mysql-0, se esperó Ready y el auto continuó visible. |
| Actualización | Se construyó/publicó v1.0.1, se aplicó Deployment y se comprobó el pie «Versión 1.0.1». Diego confirmó revisión de imagen/ready de las réplicas. |
| Sesiones compartidas | PHP como UID/GID 33 guardó una sesión en app-6876c85c89-s289x y app-6876c85c89-zhkfb leyó «sesion compartida». |
| Limpieza de prueba | Diego confirmó la ejecución de session_destroy para la sesión de prueba. |

La prueba de recuperación acredita reemplazo automático del Pod. No se midió
tráfico HTTP continuo durante ese reemplazo: no afirmar cero peticiones fallidas.
Las dos réplicas y los PVC dependen de una sola EC2 y de su disco local.
La consulta SNMP mide tiempo desde inicio del agente, no desde arranque del SO.
