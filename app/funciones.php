<?php
function escapar($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $accion, ?int $id = null): string {
    $datos = ['accion' => $accion];
    if ($id !== null) {
        $datos['id'] = $id;
    }
    return 'index.php?' . http_build_query($datos);
}

function redirigir(string $destino): void {
    header('Location: ' . $destino, true, 303);
    exit;
}

function idValido($valor): ?int {
    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}
