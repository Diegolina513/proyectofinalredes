<?php
namespace App\Controllers;

class MarcaController extends Controller {
    protected string $modulo = 'marcas';
    protected string $singular = 'marca';

    protected function normalizar(array $entrada): array {
        return ['nombre' => $this->texto($entrada, 'nombre'), 'pais_origen' => $this->texto($entrada, 'pais_origen')];
    }

    protected function validar(array $datos, ?int $id): array {
        $errores = $this->validarTexto($datos['nombre'], 'nombre', 100, true);
        $errores = [...$errores, ...$this->validarTexto($datos['pais_origen'], 'país de origen', 100)];
        if ($datos['nombre'] !== '' && $this->modelo->existeNombre($datos['nombre'], $id)) {
            $errores[] = 'Ya existe ese nombre.';
        }
        return $errores;
    }
}
