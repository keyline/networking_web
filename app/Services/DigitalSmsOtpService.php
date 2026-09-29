<?php

namespace App\Services;

use RuntimeException;

class DigitalSmsOtpService
{
    public function send(string $recipient, string $otp): array
    {
        $payload = [
            'recipient' => preg_replace('/\D+/', '', $recipient),
            'entity_id' => config('services.digital_sms.entity_id'),
            'sender_id' => config('services.digital_sms.sender_id'),
            'type' => 'transactional',
            'message' => "Dear user, {$otp} is you verification OTP for registration at KEYLINE",
            'dlt_template_id' => config('services.digital_sms.template_id'),
        ];

        $response = $this->dispatch($payload);
        $data = json_decode($response['body'], true);

        if ($response['http_status'] < 200 || $response['http_status'] >= 300) {
            throw new RuntimeException('DigitalSMS request failed with HTTP '.$response['http_status'].'.');
        }
        if (!is_array($data) || strtolower((string) ($data['status'] ?? '')) !== 'success') {
            throw new RuntimeException((string) ($data['message'] ?? 'DigitalSMS rejected the OTP request.'));
        }

        return $data;
    }

    protected function dispatch(array $payload): array
    {
        $token = (string) config('services.digital_sms.token');
        if ($token === '') {
            throw new RuntimeException('DigitalSMS bearer token is not configured.');
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => config('services.digital_sms.url'),
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '.$token,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
        ]);

        $body = curl_exec($curl);
        if ($body === false) {
            throw new RuntimeException('DigitalSMS connection failed: '.curl_error($curl));
        }

        return [
            'http_status' => (int) curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'body' => (string) $body,
        ];
    }
}
