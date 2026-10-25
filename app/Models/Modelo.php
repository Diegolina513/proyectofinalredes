<?php
namespace App\Models;
use PDO;

abstract class Modelo {
    protected string $tabla;
    protected string $pk;

    public function __construct(protected PDO $pdo) {}

    protected function consulta(string $sql, array $datos = []): \PDOStatement {
        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($datos);
        return $consulta;
    }

    public function listar(): array {
        return $this->consulta("SELECT * FROM {$this->tabla} ORDER BY {$this->pk} DESC")->fetchAll();
    }

    public function buscar(int $id): ?array {
        return $this->consulta("SELECT * FROM {$this->tabla} WHERE {$this->pk} = ?", [$id])->fetch() ?: null;
    }

    protected function existe(string $campo, string $valor, ?int $id): bool {
        return (bool) $this->consulta(
            "SELECT 1 FROM {$this->tabla} WHERE $campo = ? AND {$this->pk} <> ?",
            [$valor, $id ?? 0]
        )->fetchColumn();
    }

    public function crear(array $datos): void {
        $campos = implode(', ', array_keys($datos));
        $parametros = implode(', ', array_fill(0, count($datos), '?'));
        $this->consulta("INSERT INTO {$this->tabla} ($campos) VALUES ($parametros)", array_values($datos));
    }

    public function actualizar(int $id, array $datos): void {
        $campos = implode(', ', array_map(fn($campo) => "$campo = ?", array_keys($datos)));
        $this->consulta("UPDATE {$this->tabla} SET $campos WHERE {$this->pk} = ?", [...array_values($datos), $id]);
    }

    public function eliminar(int $id): void {
        $this->consulta("DELETE FROM {$this->tabla} WHERE {$this->pk} = ?", [$id]);
    }
}
