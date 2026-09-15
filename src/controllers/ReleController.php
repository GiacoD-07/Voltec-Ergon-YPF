<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ReleService;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Responsabilidad: traducir peticiones HTTP de relés a respuestas HTTP.
final class ReleController
{
    public function __construct(private readonly ReleService $service)
    {
    }

    /** Consulta el estado almacenado de los tres relés. */
    public function obtenerEstado(Request $request, Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'reles' => $this->service->obtenerEstado(),
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /** Decodifica la petición y delega validación y actualización al servicio. */
    public function actualizarEstado(Request $request, Response $response): Response
    {
        $body = json_decode($request->getBody()->getContents(), true);

        if (!is_array($body)) {
            return $this->jsonError($response, 'JSON inválido', 400);
        }

        try {
            $this->service->actualizar($body);
        } catch (InvalidArgumentException $exception) {
            return $this->jsonError($response, $exception->getMessage(), 422);
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'mensaje' => 'Estado de relés actualizado',
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function jsonError(Response $response, string $mensaje, int $status): Response
    {
        $response->getBody()->write(json_encode(['status' => 'error', 'mensaje' => $mensaje]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}