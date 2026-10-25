<?php
namespace App\Models;

class Auto extends Modelo {
    protected string $tabla = 'autos';
    protected string $pk = 'id_auto';

    public function listar(): array {
        return $this->consulta(
            'SELECT a.*, m.nombre AS marca, c.nombre AS categoria
             FROM autos a
             INNER JOIN marcas m ON a.id_marca = m.id_marca
             INNER JOIN categorias c ON a.id_categoria = c.id_categoria
             ORDER BY a.id_auto DESC'
        )->fetchAll();
    }

    public function buscar(int $id): ?array {
        return $this->consulta(
            'SELECT a.*, m.nombre AS marca, c.nombre AS categoria
             FROM autos a
             INNER JOIN marcas m ON a.id_marca = m.id_marca
             INNER JOIN categorias c ON a.id_categoria = c.id_categoria
             WHERE a.id_auto = ?', [$id]
        )->fetch() ?: null;
    }

    public function existePlaca(string $placa, ?int $id = null): bool {
        return $this->existe('placa', $placa, $id);
    }

    public function listarMarcas(): array {
        return $this->consulta('SELECT * FROM marcas ORDER BY nombre')->fetchAll();
    }

    public function listarCategorias(): array {
        return $this->consulta('SELECT * FROM categorias ORDER BY nombre')->fetchAll();
    }
}
