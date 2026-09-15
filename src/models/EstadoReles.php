<?php

declare(strict_types=1);

namespace App\Models;

// Responsabilidad: representar el estado binario de los relés del dispositivo.
final class EstadoReles
{
    public function __construct(
        public readonly int $rele1,
        public readonly int $rele2,
        public readonly int $rele3
    ) {
    }

    /** Convierte el modelo al formato que espera la API y el ESP32. */
    public function toArray(): array
    {
        return [
            'rele_1' => $this->rele1,
            'rele_2' => $this->rele2,
            'rele_3' => $this->rele3,
        ];
    }
}
