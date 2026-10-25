<?php
require dirname(__DIR__, 2) . '/config/Conexion.php';
header('Content-Type: application/json');
try {
    Config\Conexion::obtener()->query('SELECT 1 FROM autos LIMIT 1');
    echo json_encode(['status' => 'ready']);
} catch (Throwable $error) {
    error_log((string) $error);
    http_response_code(503);
    echo json_encode(['status' => 'unavailable']);
}
