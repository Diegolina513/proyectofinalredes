<?php
namespace App\Models;

class Marca extends Modelo {
    protected string $tabla = 'marcas';
    protected string $pk = 'id_marca';

    public function existeNombre(string $nombre, ?int $id = null): bool {
        return $this->existe('nombre', $nombre, $id);
    }

    public function tieneAutos(int $id): bool {
        return (bool) $this->consulta(
            'SELECT 1 FROM autos WHERE id_marca = ? LIMIT 1', [$id]
        )->fetchColumn();
    }
}
