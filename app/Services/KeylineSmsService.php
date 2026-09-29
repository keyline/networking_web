<?php

namespace App\Services;

use App\Models\GeneralSetting;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class KeylineSmsService
{
    protected string $senderid;
    protected string $password;
    protected string $to;
    protected string $from;
    protected Collection $lines;
    protected string $dryrun = 'no';
    protected string $baseUrl;

    /**
     * SmsMessage constructor.
     * @param array $lines
     */
    public function __construct($lines = [])
    {
        $this->lines = collect();

        // Use the same centrally managed gateway settings as admin SMS.
        $settings = GeneralSetting::find(1);
        $this->from = ''; //config('services.sms_providers.keylines.from');
        $this->baseUrl = (string) ($settings?->sms_base_url ?: config('services.sms_providers.keylines.base_url', ''));
        $this->senderid = (string) ($settings?->sms_sender_id ?: config('services.sms_providers.keylines.senderid', ''));
        $this->password = (string) ($settings?->sms_authentication_key ?: config('services.sms_providers.keylines.authkey', ''));
    }

    public function line($line = ''): self
    {
        $this->lines->push($line);

        return $this;
    }

    public function to($to): self
    {
        $this->to = $to;

        return $this;
    }

    public function from($from): self
    {
        $this->from = $from;

        return $this;
    }

    public function send(): mixed
    {
        try {

            if (!$this->baseUrl) {
                throw new \Exception('The SMS gateway URL is not configured.');
            }
            if (!$this->senderid || !$this->password) {
                throw new \Exception('The SMS sender ID or authentication key is not configured.');
            }
            if (!$this->to || !count($this->lines)) {
                throw new \Exception('The SMS recipient or message is missing.');
            }
            $postData = [
                'apikey' => $this->password,
                'number' => $this->to,
                'message'   => $this->lines->join("\n", ""),
                'senderid' => $this->senderid,
                'format' => 'json',
            ];

            $response = $this->dispatch($postData);
            $data = json_decode($response['body'], true);
            Log::info('SMS gateway response', ['http_status' => $response['http_status'], 'data' => $data ?: $response['body']]);
            if ($response['http_status'] < 200 || $response['http_status'] >= 300) {
                return ['status' => 'error', 'message' => 'Request failed with status: '.$response['http_status'], 'data' => $response['body']];
            }
            if (trim($response['body']) === '' || str_contains(strtolower($response['body']), '<html')) {
                return ['status' => 'error', 'message' => 'The SMS gateway returned an invalid response.', 'data' => $response['body']];
            }
            if (is_array($data) && in_array(strtolower((string) ($data['status'] ?? 'success')), ['error', 'failed', 'failure'], true)) {
                return ['status' => 'error', 'message' => (string) ($data['message'] ?? 'The SMS gateway rejected the message.'), 'data' => $data];
            }
            return ['status' => 'success', 'data' => $data ?: $response['body']];
        } catch (Exception $ex) {

            // Handle any other exceptions
            return [
                'status' => 'error',
                'message' => 'An unexpected error occurred: ' . $ex->getMessage(),
                'data' => null,
            ];
        }
    }

    /** Use the identical multipart cURL request used by the working admin sender. */
    protected function dispatch(array $postData): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => false,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($curl);
        if ($body === false) {
            $message = curl_error($curl);
            curl_close($curl);
            throw new Exception('SMS connection failed: '.$message);
        }
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return ['http_status' => $status, 'body' => (string) $body];
    }

    public function dryrun($dry = 'yes'): self
    {
        $this->dryrun = $dry;

        return $this;
    }
}
