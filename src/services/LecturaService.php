<?php

declare(strict_types=1);

namespace App\Services;

use App\Integrations\GoogleSheetsClient;
use App\Models\Lectura;
use App\Persistence\LecturaRepository;
use InvalidArgumentException;

// Responsabilidad: aplicar las reglas de negocio de las lecturas y coordinar sus colaboradores.
final class LecturaService
{
    public function __construct(
        private readonly LecturaRepository $repository,
        private readonly GoogleSheetsClient $googleSheets
    ) {
    }

    /** Devuelve datos con valores iniciales cuando todavía no existen lecturas. */
    public function obtenerUltima(): array
    {
        $lectura = $this->repository->obtenerUltima() ?? [
            'voltaje' => 220.0,
            'corriente' => 0.0,
            'potencia_activa' => 0.0,
            'energia_total_kwh' => 0.0,
            'consumo_fantasma' => 0,
        ];

        $factorEmision = (float) ($_ENV['CO2_EMISSION_FACTOR_KG_PER_KWH'] ?? 0.39);
        if ($factorEmision < 0) {
            $factorEmision = 0.39;
        }

        $lectura['factor_emision_co2_kg_kwh'] = $factorEmision;
        $lectura['huella_carbono_kg'] = round(
            max(0.0, (float) $lectura['energia_total_kwh']) * $factorEmision,
            3
        );

        return $lectura;
    }

    /** Valida, construye, persiste y replica una lectura. */
    public function guardar(array $body): void
    {
        $dispositivoId = $body['dispositivo_id'] ?? 'EcoSmart_01';
        $values = [
            'voltaje' => $body['voltaje'] ?? null,
            'corriente' => $body['corriente'] ?? null,
            'potencia_activa' => $body['potencia_activa'] ?? null,
            'energia_total_kwh' => $body['energia_total_kwh'] ?? null,
        ];

        if (!is_string($dispositivoId) || $dispositivoId === '' || strlen($dispositivoId) > 50
            || array_filter($values, static fn($value): bool => !is_numeric($value)) !== []) {
            throw new InvalidArgumentException('Datos de lectura inválidos');
        }

        $voltaje = (float) $values['voltaje'];
        $corriente = (float) $values['corriente'];
        $potencia = (float) $values['potencia_activa'];
        $energia = (float) $values['energia_total_kwh'];

        if ($voltaje < 0 || $corriente < 0 || $potencia < 0 || $energia < 0) {
            throw new InvalidArgumentException('Las mediciones no pueden ser negativas');
        }

        $consumoFantasma = $potencia > 0.5 && $potencia < 15.0;
        $lectura = new Lectura(
            $dispositivoId,
            $voltaje,
            $corriente,
            $potencia,
            $energia,
            $consumoFantasma
        );

        $this->repository->guardar($lectura);
        $this->googleSheets->enviarLectura([
            'dispositivo' => $lectura->dispositivoId,
            'voltaje' => $lectura->voltaje,
            'corriente' => $lectura->corriente,
            'potencia' => $lectura->potenciaActiva,
            'energia' => $lectura->energiaTotalKwh,
            'consumo_fantasma' => $lectura->consumoFantasma ? 1 : 0,
            'fecha' => date('Y-m-d H:i:s'),
        ]);
    }
}
