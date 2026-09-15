<?php

// Carga la aplicación Slim y entrega la petición al enrutador del backend.
$app = require __DIR__ . "/../src/bootstrap.php";

$app->run();
