<?php
namespace App\Controllers;

class CategoriaController extends Controller {
    protected string $modulo = 'categorias';
    protected string $singular = 'categoria';

    protected function normalizar(array $entrada): array {
        return ['nombre' => $this->texto($entrada, 'nombre'), 'descripcion' => $this->texto($entrada, 'descripcion')];
    }

    protected function validar(array $datos, ?int $id): array {
        $errores = $this->validarTexto($datos['nombre'], 'nombre', 100, true);
        $errores = [...$errores, ...$this->validarTexto($datos['descripcion'], 'descripción', 500)];
        if ($datos['nombre'] !== '' && $this->modelo->existeNombre($datos['nombre'], $id)) {
            $errores[] = 'Ya existe ese nombre.';
        }
        return $errores;
    }
}
