<?php

namespace App\Services;

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

        // Pull in config from the config/services.php file.
        $this->from = ''; //config('services.sms_providers.keylines.from');
        $this->baseUrl = config('services.sms_providers.keylines.base_url');
        $this->senderid = config('services.sms_providers.keylines.senderid');
        $this->password = config('services.sms_providers.keylines.authkey');
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

            if (!$this->to || !count($this->lines)) {
                throw new \Exception('SMS not correct.');
            }
            $postData = [
                //'apikey' => $this->password,
                'entity_id' => '1201159375531154788',
                "sender_id" => "KEYLNS",
                'type'      => 'transactional',
                'recipient' => $this->to,
                'message'   => $this->lines->join("\n", ""),
                // 'sender_id' => $this->senderid,
                'dlt_template_id' => '1307162333099680070'
            ];


            $response = Http::withToken('198|td0aaBizzgjMwRgKcQfn8VTYguWUXCs2fo6hSsYIabc9f13f')
                ->withOptions([
                    'verify' => false, // Disable SSL verification
                ])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->baseUrl, $postData);



            if ($response->successful()) {

                Log::info('API Response:', [
                    'status' => $response->status(),
                    'data' => $response->json(),
                ]);

                return $response->json();
            }


            // Handle unsuccessful responses
            return [
                'status' => 'error',
                'message' => 'Request failed with status: ' . $response->status(),
                'data' => null,
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
