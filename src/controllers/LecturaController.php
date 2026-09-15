<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\LecturaService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Responsabilidad: traducir peticiones HTTP de lecturas a respuestas HTTP.
final class LecturaController
{
    public function __construct(private readonly LecturaService $service)
    {
    }

    /** Devuelve la medición más reciente para actualizar el panel. */
    public function obtenerUltimaLectura(Request $request, Response $response): Response
    {
        $response->getBody()->write(json_encode($this->service->obtenerUltima()));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /** Decodifica la petición y delega validación y persistencia al servicio. */
    public function guardarLectura(Request $request, Response $response): Response
    {
        $body = json_decode($request->getBody()->getContents(), true);
        if (!is_array($body)) {
            return $this->jsonError($response, 'JSON inválido', 400);
        }

        try {
            $this->service->guardar($body);
        } catch (InvalidArgumentException $exception) {
            return $this->jsonError($response, $exception->getMessage(), 422);
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'mensaje' => 'Lectura guardada correctamente',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    private function jsonError(Response $response, string $mensaje, int $status): Response
    {
        $response->getBody()->write(json_encode(['status' => 'error', 'mensaje' => $mensaje]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}