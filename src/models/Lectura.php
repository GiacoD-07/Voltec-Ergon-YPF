<?php

declare(strict_types=1);

namespace App\Models;

// Responsabilidad: representar una medición eléctrica válida dentro del dominio.
final class Lectura
{
    public function __construct(
        public readonly string $dispositivoId,
        public readonly float $voltaje,
        public readonly float $corriente,
        public readonly float $potenciaActiva,
        public readonly float $energiaTotalKwh,
        public readonly bool $consumoFantasma,
        public readonly ?float $corrienteRele1 = null,
        public readonly ?float $potenciaRele1 = null,
        public readonly ?float $corrienteRele2 = null,
        public readonly ?float $potenciaRele2 = null,
        public readonly ?float $corrienteRele3 = null,
        public readonly ?float $potenciaRele3 = null
    ) {
    }
}
