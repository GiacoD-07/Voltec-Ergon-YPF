<?php

// Responsabilidad: definir qué URL, método HTTP y controlador atienden cada operación.
use Slim\App;
use App\Controllers\LecturaController;
use App\Controllers\ReleController;
use App\Middleware\ApiKeyMiddleware;

return function (App $app, LecturaController $lecturaController, ReleController $releController) {
    // Entrega un token temporal al panel. La cookie queda protegida y el token
    // debe repetirse en el encabezado de las peticiones POST.
    $app->get('/api/web-token', function ($request, $response) {
        $token = bin2hex(random_bytes(32));
        $isHttps = $request->getUri()->getScheme() === 'https';
        $cookie = 'voltec_csrf=' . rawurlencode($token)
            . '; Path=/; Max-Age=3600; HttpOnly; SameSite=Strict'
            . ($isHttps ? '; Secure' : '');

        $response->getBody()->write(json_encode(['token' => $token]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store')
            ->withHeader('Set-Cookie', $cookie);
    });

    // El ESP32 publica mediciones y el panel consulta la última disponible.
    $app->get('/api/lectura', [$lecturaController, 'obtenerUltimaLectura']);
    $app->post('/api/lectura', [$lecturaController, 'guardarLectura'])
        ->add(new ApiKeyMiddleware());

    // El ESP32 lee el estado de los relés y el panel puede actualizarlo.
    $app->get('/api/control-reles', [$releController, 'obtenerEstado']);
    $app->post('/api/control-reles', [$releController, 'actualizarEstado'])
        ->add(new ApiKeyMiddleware());
};