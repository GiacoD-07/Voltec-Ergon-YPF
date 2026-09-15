<?php

// Responsabilidad: servir archivos estáticos y reenviar las rutas dinámicas a Slim durante el desarrollo.
// Este router solo se usa con el servidor PHP de desarrollo.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;

// Los archivos existentes se sirven directamente como CSS, JS, imágenes, etc.
if ($path !== '/' && is_file($file)) {
    return false;
}

if ($path === '/') {
    // La pantalla principal es un archivo HTML estático.
    readfile(__DIR__ . '/index.html');
    return true;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
