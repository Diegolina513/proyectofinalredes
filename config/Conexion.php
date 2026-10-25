<?php
namespace Config;
use PDO;
use RuntimeException;

class Conexion {
    public static function obtener(): PDO {
        $host = getenv('DB_HOST') ?: 'db';
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_NAME') ?: 'concesionaria_mvc';
        $user = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');
        if ($user === false || $user === '' || $password === false || $password === '') {
            throw new RuntimeException('Faltan DB_USER y/o DB_PASSWORD.');
        }
        return new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
}
