<?php
namespace App\Database;

use PDO;
use PDOException;

// Responsabilidad: entregar a la aplicación una conexión PDO configurada para el motor elegido.
class Connection {
    private static ?PDO $instance = null;

    /** Crea una única conexión PDO reutilizable durante la petición. */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $driver = $_ENV['DB_DRIVER'] ?? 'mysql';
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $db   = $_ENV['DB_NAME'] ?? 'ypf_energia';
            $user = $_ENV['DB_USER'] ?? 'root';
            $pass = $_ENV['DB_PASS'] ?? '';
            $port = $_ENV['DB_PORT'] ?? ($driver === 'pgsql' ? '5432' : '3306');

            // El DSN cambia según el motor configurado en .env.
            if ($driver === 'pgsql') {
                $dsn = "pgsql:host=$host;port=$port;dbname=$db";
            } elseif ($driver === 'mysql') {
                $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
            } else {
                throw new \RuntimeException("Driver PDO no soportado: {$driver}");
            }

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            // Las opciones fuerzan errores como excepciones y consultas preparadas reales.
            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new PDOException('Error de conexión a la Base de Datos.', 0, $e);
            }
        }
        return self::$instance;
    }
}