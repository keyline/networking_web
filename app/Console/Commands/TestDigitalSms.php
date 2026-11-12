<?php

namespace App\Console\Commands;

use App\Services\DigitalSmsOtpService;
use Illuminate\Console\Command;

/**
 * Diagnose member-login SMS OTP delivery on a server:
 *   php artisan sms:test               -> checks config + gateway reachability (sends nothing)
 *   php artisan sms:test 98XXXXXXXX    -> also sends one test OTP (123456) and prints the gateway's reply
 */
class TestDigitalSms extends Command
{
    protected $signature = 'sms:test {mobile? : 10-digit mobile to send a test OTP to}';

    protected $description = 'Check DigitalSMS settings and connectivity, optionally sending one test OTP.';

    public function handle(DigitalSmsOtpService $sms): int
    {
        $this->line('1) Settings (values hidden):');
        $missing = [];
        foreach (['url', 'token', 'entity_id', 'sender_id', 'template_id', 'type'] as $key) {
            $value = trim((string) config('services.digital_sms.'.$key));
            if ($value === '') {
                $missing[] = $key;
            }
            $shown = $value === '' ? '<fg=red>MISSING</>' : (in_array($key, ['url', 'type'], true) ? $value : 'set ('.strlen($value).' chars)');
            $this->line(sprintf('   %-12s %s', $key, $shown));
        }
        if ($missing) {
            $this->error('   Add these to .env (DIGITAL_SMS_TOKEN, DIGITAL_SMS_ENTITY_ID, DIGITAL_SMS_SENDER_ID, DIGITAL_SMS_TEMPLATE_ID), then run: php artisan config:clear');
        }

        $this->line('2) Can this server reach the gateway?');
        $curl = curl_init((string) config('services.digital_sms.url'));
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '{}',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
        ]);
        $started = microtime(true);
        $body = curl_exec($curl);
        $seconds = microtime(true) - $started;
        if ($body === false) {
            $this->error('   NO - '.curl_error($curl).' (check the server firewall / outbound HTTPS, or ask the host to allow sms.digitalsms.net)');
            curl_close($curl);

            return self::FAILURE;
        }
        $this->info(sprintf('   yes - HTTP %d in %.1fs (no SMS sent by this check)', curl_getinfo($curl, CURLINFO_HTTP_CODE), $seconds));
        curl_close($curl);

        $mobile = $this->argument('mobile');
        if (! $mobile) {
            $this->line('3) Skipped sending. Run "php artisan sms:test <mobile>" to send one test OTP.');

            return $missing ? self::FAILURE : self::SUCCESS;
        }

        $this->line('3) Sending test OTP 123456 to ...'.substr(preg_replace('/\D+/', '', $mobile), -4));
        try {
            $reply = $sms->send($mobile, '123456');
            $this->info('   Gateway accepted: '.json_encode($reply));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('   Gateway/config error: '.$e->getMessage());
            $this->line('   Common causes: wrong token, DLT template text not matching the approved template, low SMS balance, server IP not whitelisted with DigitalSMS.');

            return self::FAILURE;
        }
    }
}
