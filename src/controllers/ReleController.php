<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Database\Connection;

class ReleController {

    // GET /api/control-reles
    public function obtenerEstado(Request $request, Response $response): Response {
        $pdo = Connection::getConnection();
        $stmt = $pdo->prepare("SELECT rele_1, rele_2, rele_3 FROM control_reles WHERE dispositivo_id = 'EcoSmart_01' LIMIT 1");
        $stmt->execute();
        $estado = $stmt->fetch();

        if (!$estado) {
            $estado = ['rele_1' => 0, 'rele_2' => 0, 'rele_3' => 0];
        }

        $response->getBody()->write(json_encode(['status' => 'success', 'reles' => $estado]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // POST /api/control-reles
    public function actualizarEstado(Request $request, Response $response): Response {
        $body = json_decode($request->getBody()->getContents(), true);

        $rele1 = isset($body['rele_1']) ? intval($body['rele_1']) : null;
        $rele2 = isset($body['rele_2']) ? intval($body['rele_2']) : null;
        $rele3 = isset($body['rele_3']) ? intval($body['rele_3']) : null;

        $pdo = Connection::getConnection();
        
        $fields = [];
        $params = [];

        if ($rele1 !== null) { $fields[] = "rele_1 = ?"; $params[] = $rele1; }
        if ($rele2 !== null) { $fields[] = "rele_2 = ?"; $params[] = $rele2; }
        if ($rele3 !== null) { $fields[] = "rele_3 = ?"; $params[] = $rele3; }

        if (!empty($fields)) {
            $sql = "UPDATE control_reles SET " . implode(', ', $fields) . " WHERE dispositivo_id = 'EcoSmart_01'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        $response->getBody()->write(json_encode(['status' => 'success', 'mensaje' => 'Estado de relés actualizado']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}