<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

// Responsabilidad: proporcionar la conexión PDO que utiliza el ejecutor de migraciones.
final class Database
{
    private ?PDO $connection = null;

    /** Abre y conserva la conexión usada por el comando de migraciones. */
    public function getConnection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $driver = $_ENV['DB_DRIVER'] ?? 'mysql';
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? ($driver === 'pgsql' ? '5432' : '3306');
        $name = $_ENV['DB_NAME'] ?? 'ypf_energia';
        $user = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';

        // Genera el DSN compatible con el motor indicado por DB_DRIVER.
        if ($driver === 'pgsql') {
            $dsn = "pgsql:host={$host};port={$port};dbname={$name}";
        } elseif ($driver === 'mysql') {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        } else {
            throw new \RuntimeException("Driver PDO no soportado: {$driver}");
        }

        try {
            $this->connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new PDOException('No se pudo conectar con la base de datos.', 0, $exception);
        }

        return $this->connection;
    }
}
