<?php
session_start();
date_default_timezone_set('America/Guatemala');

spl_autoload_register(function(string $clase): void {
    $partes = explode('\\', $clase);
    $raiz = array_shift($partes);
    $carpeta = ['App' => 'app', 'Config' => 'config'][$raiz] ?? null;
    if ($carpeta) {
        $archivo = dirname(__DIR__) . '/' . $carpeta . '/' . implode('/', $partes) . '.php';
        if (is_file($archivo)) {
            require $archivo;
        }
    }
});
require dirname(__DIR__) . '/app/funciones.php';

$accion = is_string($_GET['accion'] ?? null) ? $_GET['accion'] : 'autos';
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    http_response_code(405);
    header('Allow: GET, POST');
    exit('Método no permitido.');
}

try {
    $pdo = Config\Conexion::obtener();
    $autoController = new App\Controllers\AutoController(new App\Models\Auto($pdo));
    $marcaController = new App\Controllers\MarcaController(new App\Models\Marca($pdo));
    $categoriaController = new App\Controllers\CategoriaController(new App\Models\Categoria($pdo));
    require dirname(__DIR__) . '/routes/web.php';
} catch (Throwable $error) {
    error_log((string) $error);
    http_response_code(500);
    echo 'No se pudo completar la solicitud. Revise la conexión y la base de datos.';
}
