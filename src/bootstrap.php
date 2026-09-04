<?php
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno de .env si existe
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

$app = AppFactory::create();

// Parsear automáticamente cuerpos de peticiones JSON
$app->addBodyParsingMiddleware();

// Activar manejo de errores para desarrollo
$app->addErrorMiddleware(true, true, true);

// Registrar las rutas
$routes = require __DIR__ . '/routes/api.php';
$routes($app);

return $app;