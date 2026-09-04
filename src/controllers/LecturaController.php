<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Database\Connection;
use App\Utils\GoogleSheets;
use PDO;

class LecturaController {

    // GET /api/lectura (Consulta la última lectura para la PWA)
    public function obtenerUltimaLectura(Request $request, Response $response): Response {
        $pdo = Connection::getConnection();
        $stmt = $pdo->query("SELECT * FROM historial_consumo ORDER BY id DESC LIMIT 1");
        $lectura = $stmt->fetch();

        if (!$lectura) {
            $lectura = [
                'voltaje' => 220.0,
                'corriente' => 0.0,
                'potencia_activa' => 0.0,
                'energia_total_kwh' => 0.0,
                'consumo_fantasma' => 0
            ];
        }

        $response->getBody()->write(json_encode($lectura));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // POST /api/lectura (Guarda nueva lectura desde el ESP32)
    public function guardarLectura(Request $request, Response $response): Response {
        $body = json_decode($request->getBody()->getContents(), true);

        $dispositivoId = $body['dispositivo_id'] ?? 'EcoSmart_01';
        $voltaje = floatval($body['voltaje'] ?? 0);
        $corriente = floatval($body['corriente'] ?? 0);
        $potencia = floatval($body['potencia_activa'] ?? 0);
        $energia = floatval($body['energia_total_kwh'] ?? 0);

        // Detección lógica de Consumo Fantasma (ejemplo: Potencia entre 0.5W y 15W en standby)
        $consumoFantasma = ($potencia > 0.5 && $potencia < 15.0) ? 1 : 0;

        $pdo = Connection::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO historial_consumo (dispositivo_id, voltaje, corriente, potencia_activa, energia_total_kwh, consumo_fantasma)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$dispositivoId, $voltaje, $corriente, $potencia, $energia, $consumoFantasma]);

        // Envío asincrónico/secundario a Google Sheets
        GoogleSheets::enviarLectura([
            'dispositivo' => $dispositivoId,
            'voltaje' => $voltaje,
            'corriente' => $corriente,
            'potencia' => $potencia,
            'energia' => $energia,
            'consumo_fantasma' => $consumoFantasma,
            'fecha' => date('Y-m-d H:i:s')
        ]);

        $response->getBody()->write(json_encode(['status' => 'success', 'mensaje' => 'Lectura guardada correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}