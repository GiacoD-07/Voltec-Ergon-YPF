<?php
use Slim\App;
use App\Controllers\LecturaController;
use App\Controllers\ReleController;

return function (App $app) {
    // Endpoints de Lectura
    $app->get('/api/lectura', [LecturaController::class, 'obtenerUltimaLectura']);
    $app->post('/api/lectura', [LecturaController::class, 'guardarLectura']);

    // Endpoints de Relés
    $app->get('/api/control-reles', [ReleController::class, 'obtenerEstado']);
    $app->post('/api/control-reles', [ReleController::class, 'actualizarEstado']);
};