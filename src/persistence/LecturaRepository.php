<?php

declare(strict_types=1);

namespace App\Persistence;

use App\Models\Lectura;
use PDO;

// Responsabilidad: consultar y guardar lecturas sin conocer HTTP ni reglas de presentación.
final class LecturaRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** Devuelve la lectura más reciente o null si todavía no hay registros. */
    public function obtenerUltima(): ?array
    {
        $stmt = $this->pdo->query(
                'SELECT voltaje, corriente, potencia_activa, energia_total_kwh, consumo_fantasma,
                    corriente_rele_1, potencia_rele_1, corriente_rele_2, potencia_rele_2,
                    corriente_rele_3, potencia_rele_3
             FROM historial_consumo ORDER BY id DESC LIMIT 1'
        );

        $lectura = $stmt->fetch();
        return $lectura === false ? null : $lectura;
    }

    /** Persiste una lectura ya validada por el servicio de dominio. */
    public function guardar(Lectura $lectura): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO historial_consumo
                     (dispositivo_id, voltaje, corriente, potencia_activa, energia_total_kwh, consumo_fantasma,
                      corriente_rele_1, potencia_rele_1, corriente_rele_2, potencia_rele_2,
                      corriente_rele_3, potencia_rele_3)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $lectura->dispositivoId,
            $lectura->voltaje,
            $lectura->corriente,
            $lectura->potenciaActiva,
            $lectura->energiaTotalKwh,
            $lectura->consumoFantasma ? 1 : 0,
            $lectura->corrienteRele1,
            $lectura->potenciaRele1,
            $lectura->corrienteRele2,
            $lectura->potenciaRele2,
            $lectura->corrienteRele3,
            $lectura->potenciaRele3,
        ]);
    }
}
