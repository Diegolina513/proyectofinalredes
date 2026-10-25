<?php
namespace App\Models;

class Categoria extends Modelo {
    protected string $tabla = 'categorias';
    protected string $pk = 'id_categoria';

    public function existeNombre(string $nombre, ?int $id = null): bool {
        return $this->existe('nombre', $nombre, $id);
    }

    public function tieneAutos(int $id): bool {
        return (bool) $this->consulta(
            'SELECT 1 FROM autos WHERE id_categoria = ? LIMIT 1', [$id]
        )->fetchColumn();
    }
}
