<?php

namespace App\Services;

use RuntimeException;
use Illuminate\Support\Facades\Log;

class DigitalSmsOtpService
{
    public function send(string $recipient, string $otp): array
    {
        $recipient = preg_replace('/\D+/', '', $recipient);
        if (strlen($recipient) === 10) {
            $recipient = '91'.$recipient;
        }
        if (!preg_match('/^91[6-9][0-9]{9}$/', $recipient)) {
            throw new RuntimeException('The SMS recipient must be a valid Indian mobile number.');
        }

        foreach (['token', 'entity_id', 'sender_id', 'template_id'] as $key) {
            if (trim((string) config('services.digital_sms.'.$key)) === '') {
                throw new RuntimeException('DigitalSMS '.$key.' is not configured.');
            }
        }

        $payload = [
            'recipient' => $recipient,
            'entity_id' => config('services.digital_sms.entity_id'),
            'sender_id' => config('services.digital_sms.sender_id'),
            'type' => config('services.digital_sms.type', 'transactional'),
            'message' => "Dear User, {$otp} is you verification OTP for registration at KEYLINE",
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

        Log::info('DigitalSMS OTP accepted', [
            'recipient' => substr($payload['recipient'], -4),
            'http_status' => $response['http_status'],
        ]);

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
            $error = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException('DigitalSMS connection failed: '.$error);
        }

        $result = [
            'http_status' => (int) curl_getinfo($curl, CURLINFO_HTTP_CODE),
            'body' => (string) $body,
        ];
        curl_close($curl);

        return $result;
    }
}
