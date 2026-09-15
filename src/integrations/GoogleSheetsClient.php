<?php

declare(strict_types=1);

namespace App\Integrations;

// Responsabilidad: encapsular la comunicación opcional con el webhook de Google Sheets.
final class GoogleSheetsClient
{
    public function enviarLectura(array $data): void
    {
        $webhookUrl = $_ENV['GOOGLE_WEBHOOK_URL'] ?? '';
        if ($webhookUrl === '') {
            return;
        }

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_exec($ch);
        curl_close($ch);
    }
}
