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
        public readonly bool $consumoFantasma
    ) {
    }
}
