<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class ApiKeyMiddleware
{
    // Responsabilidad: impedir que una petición de escritura llegue al controlador sin autorización.
    // Permite reutilizar el middleware con otra variable de entorno si fuera necesario.
    public function __construct(private readonly string $environmentVariable = 'API_KEY')
    {
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // La API key identifica al ESP32; el token CSRF autoriza al panel web.
        $expectedKey = $_ENV[$this->environmentVariable] ?? '';
        $providedKey = $request->getHeaderLine('X-API-Key');
        $cookies = $request->getCookieParams();
        $csrfCookie = $cookies['voltec_csrf'] ?? '';
        $csrfHeader = $request->getHeaderLine('X-CSRF-Token');

        $validApiKey = $expectedKey !== ''
            && $providedKey !== ''
            && hash_equals($expectedKey, $providedKey);
        $validCsrfToken = $csrfCookie !== ''
            && $csrfHeader !== ''
            && hash_equals($csrfCookie, $csrfHeader);

        // Se rechaza la petición si no presenta ninguno de los dos mecanismos válidos.
        if (!$validApiKey && !$validCsrfToken) {
            $response = new Response(401);
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'mensaje' => 'API key inválida o ausente',
            ]));

            return $response->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
