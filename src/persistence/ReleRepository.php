<?php

declare(strict_types=1);

namespace App\Persistence;

use App\Models\EstadoReles;
use PDO;

// Responsabilidad: leer y actualizar el estado de relés en la base de datos.
final class ReleRepository
{
    private const DISPOSITIVO_ID = 'EcoSmart_01';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function obtenerEstado(): ?EstadoReles
    {
        $stmt = $this->pdo->prepare(
            'SELECT rele_1, rele_2, rele_3 FROM control_reles
             WHERE dispositivo_id = ? LIMIT 1'
        );
        $stmt->execute([self::DISPOSITIVO_ID]);
        $estado = $stmt->fetch();

        if ($estado === false) {
            return null;
        }

        return new EstadoReles(
            (int) $estado['rele_1'],
            (int) $estado['rele_2'],
            (int) $estado['rele_3']
        );
    }

    /** Actualiza únicamente los campos recibidos por el servicio. */
    public function actualizar(array $campos): void
    {
        if ($campos === []) {
            return;
        }

        $asignaciones = [];
        $parametros = [];
        foreach ($campos as $campo => $valor) {
            $asignaciones[] = $campo . ' = ?';
            $parametros[] = $valor;
        }
        $parametros[] = self::DISPOSITIVO_ID;

        $stmt = $this->pdo->prepare(
            'UPDATE control_reles SET ' . implode(', ', $asignaciones)
            . ' WHERE dispositivo_id = ?'
        );
        $stmt->execute($parametros);
    }
}
