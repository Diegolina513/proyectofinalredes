<?php
namespace App\Controllers;

class AutoController extends Controller {
    protected string $modulo = 'autos';
    protected string $singular = 'auto';

    protected function normalizar(array $entrada): array {
        $datos = [];
        foreach (['placa', 'linea', 'modelo', 'color', 'precio', 'estado', 'id_marca', 'id_categoria'] as $campo) {
            $datos[$campo] = $this->texto($entrada, $campo);
        }
        $datos['placa'] = strtoupper($datos['placa']);
        return $datos;
    }

    protected function datosVista(): array {
        return ['marcas' => $this->modelo->listarMarcas(), 'categorias' => $this->modelo->listarCategorias()];
    }

    protected function validar(array $datos, ?int $id): array {
        $errores = [];
        foreach (['placa' => 20, 'linea' => 100, 'color' => 50] as $campo => $maximo) {
            $errores = [...$errores, ...$this->validarTexto($datos[$campo], $campo, $maximo, true)];
        }
        $modelo = filter_var($datos['modelo'], FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1886, 'max_range' => (int) date('Y') + 1]]);
        if ($modelo === false) {
            $errores[] = 'El modelo debe ser un año entre 1886 y el próximo año.';
        }
        if (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $datos['precio'])) {
            $errores[] = 'El precio debe ser no negativo y tener hasta dos decimales.';
        }
        if (!in_array($datos['estado'], ['Disponible', 'Vendido'], true)) {
            $errores[] = 'Seleccione un estado válido.';
        }
        $marca = \idValido($datos['id_marca']);
        $categoria = \idValido($datos['id_categoria']);
        if (!$marca || !in_array($marca, array_column($this->modelo->listarMarcas(), 'id_marca'))) {
            $errores[] = 'Seleccione una marca existente.';
        }
        if (!$categoria || !in_array($categoria, array_column($this->modelo->listarCategorias(), 'id_categoria'))) {
            $errores[] = 'Seleccione una categoría existente.';
        }
        if ($datos['placa'] !== '' && $this->modelo->existePlaca($datos['placa'], $id)) {
            $errores[] = 'Ya existe esa placa.';
        }
        return $errores;
    }
}
