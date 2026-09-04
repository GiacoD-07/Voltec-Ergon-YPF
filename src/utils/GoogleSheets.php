<?php
namespace App\Utils;

class GoogleSheets {
    public static function enviarLectura(array $data): void {
        $webhookUrl = $_ENV['GOOGLE_WEBHOOK_URL'] ?? '';
        if (empty($webhookUrl)) return;

        $ch = curl_init($webhookUrl);
        $payload = json_encode($data);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Timeout rápido para no demorar la respuesta de la API

        curl_exec($ch);
        curl_close($ch);
    }
}