<?php

declare(strict_types=1);

namespace App\Services;

use App\Persistence\ReleRepository;
use InvalidArgumentException;

// Responsabilidad: validar órdenes de relés y coordinar la lectura y persistencia de su estado.
final class ReleService
{
    public function __construct(private readonly ReleRepository $repository)
    {
    }

    /** Devuelve el estado actual o un estado seguro con todos los relés apagados. */
    public function obtenerEstado(): array
    {
        return $this->repository->obtenerEstado()?->toArray()
            ?? ['rele_1' => 0, 'rele_2' => 0, 'rele_3' => 0];
    }

    /** Valida una actualización parcial y la entrega al repositorio. */
    public function actualizar(array $body): void
    {
        $campos = [];
        foreach (['rele_1', 'rele_2', 'rele_3'] as $campo) {
            $valor = $body[$campo] ?? null;
            if ($valor !== null && !in_array($valor, [0, 1, '0', '1'], true)) {
                throw new InvalidArgumentException('El estado de un relé debe ser 0 o 1');
            }
            if ($valor !== null) {
                $campos[$campo] = (int) $valor;
            }
        }

        $this->repository->actualizar($campos);
    }
}
