<?php
$rutas = [
    'autos' => [$autoController, 'index'],
    'auto-ver' => [$autoController, 'ver'],
    'auto-crear' => [$autoController, 'crear'],
    'auto-editar' => [$autoController, 'editar'],
    'auto-eliminar' => [$autoController, 'eliminar'],
    'marcas' => [$marcaController, 'index'],
    'marca-ver' => [$marcaController, 'ver'],
    'marca-crear' => [$marcaController, 'crear'],
    'marca-editar' => [$marcaController, 'editar'],
    'marca-eliminar' => [$marcaController, 'eliminar'],
    'categorias' => [$categoriaController, 'index'],
    'categoria-ver' => [$categoriaController, 'ver'],
    'categoria-crear' => [$categoriaController, 'crear'],
    'categoria-editar' => [$categoriaController, 'editar'],
    'categoria-eliminar' => [$categoriaController, 'eliminar']
];
if (!isset($rutas[$accion])) {
    http_response_code(404);
    exit('Ruta no encontrada.');
}
[$controlador, $metodo] = $rutas[$accion];
$controlador->$metodo();
