<?php

// Responsabilidad: construir y configurar la aplicación HTTP antes de atender peticiones.
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use App\Controllers\LecturaController;
use App\Controllers\ReleController;
use App\Database\Connection;
use App\Integrations\GoogleSheetsClient;
use App\Persistence\LecturaRepository;
use App\Persistence\ReleRepository;
use App\Services\LecturaService;
use App\Services\ReleService;

require __DIR__ . '/../vendor/autoload.php';

// Carga la configuración local sin sobrescribir variables del entorno del servidor.
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

$app = AppFactory::create();

// Calcula la subcarpeta pública para que las rutas funcionen en XAMPP y en localhost:8000.
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = preg_replace('#/index\.php$#', '', $scriptName) ?: '';
$app->setBasePath(rtrim($basePath, '/'));

// Permite acceder a los datos JSON enviados por el ESP32 y por el panel web.
$app->addBodyParsingMiddleware();

// Muestra detalles de errores solo fuera de producción.
$displayErrors = ($_ENV['APP_ENV'] ?? 'production') !== 'production';
$app->addErrorMiddleware($displayErrors, true, $displayErrors);

// Construye las dependencias una sola vez y las inyecta hacia las capas superiores.
$pdo = Connection::getConnection();
$lecturaService = new LecturaService(new LecturaRepository($pdo), new GoogleSheetsClient());
$releService = new ReleService(new ReleRepository($pdo));
$lecturaController = new LecturaController($lecturaService);
$releController = new ReleController($releService);

// Registra todos los endpoints con controllers ya configurados.
$routes = require __DIR__ . '/routes/api.php';
$routes($app, $lecturaController, $releController);

return $app;