<?php

namespace App\Services;

use App\Models\GeneralSetting;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
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

            // Match the working admin sender: submit gateway fields as form data.
            $response = Http::asForm()->withOptions(['verify' => false])->post($this->baseUrl, $postData);



            if ($response->successful()) {

                Log::info('API Response:', [
                    'status' => $response->status(),
                    'data' => $response->json(),
                ]);

                $data = $response->json();
                if (is_array($data) && in_array(strtolower((string) ($data['status'] ?? 'success')), ['error', 'failed', 'failure'], true)) {
                    return ['status' => 'error', 'message' => (string) ($data['message'] ?? 'The SMS gateway rejected the message.'), 'data' => $data];
                }
                return ['status' => 'success', 'data' => $data ?: $response->body()];
            }


            // Handle unsuccessful responses
            return [
                'status' => 'error',
                'message' => 'Request failed with status: ' . $response->status(),
                'data' => $response->body(),
            ];
        } catch (RequestException $ex) {

            // Handle request-specific exceptions such as connection errors
            return [
                'status' => 'error',
                'message' => 'Request failed: ' . $ex->getMessage(),
                'data' => null,
            ];
        } catch (Exception $ex) {

            // Handle any other exceptions
            return [
                'status' => 'error',
                'message' => 'An unexpected error occurred: ' . $ex->getMessage(),
                'data' => null,
            ];
        }
    }

    public function dryrun($dry = 'yes'): self
    {
        $this->dryrun = $dry;

        return $this;
    }
}
