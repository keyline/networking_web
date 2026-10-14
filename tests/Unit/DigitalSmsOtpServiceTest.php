<?php

namespace Tests\Unit;

use App\Services\DigitalSmsOtpService;
use Tests\TestCase;

class DigitalSmsOtpServiceTest extends TestCase
{
    public function test_it_matches_the_mobile_apps_dlt_payload(): void
    {
        config([
            'services.digital_sms.entity_id' => 'entity',
            'services.digital_sms.sender_id' => 'KEYLNS',
            'services.digital_sms.template_id' => 'template',
        ]);

        $service = new class extends DigitalSmsOtpService {
            public array $payload = [];
            protected function dispatch(array $payload): array
            {
                $this->payload = $payload;
                return ['http_status' => 200, 'body' => '{"status":"success"}'];
            }
        };

        $result = $service->send('+91 93301-09091', '1234');

        $this->assertSame('success', $result['status']);
        $this->assertSame('919330109091', $service->payload['recipient']);
        $this->assertSame('entity', $service->payload['entity_id']);
        $this->assertSame('KEYLNS', $service->payload['sender_id']);
        $this->assertSame('transactional', $service->payload['type']);
        $this->assertSame('template', $service->payload['dlt_template_id']);
        $this->assertSame('Dear User, 1234 is you verification OTP for registration at KEYLINE', $service->payload['message']);
    }

    public function test_it_adds_the_indian_country_code_to_a_ten_digit_mobile_number(): void
    {
        config([
            'services.digital_sms.entity_id' => 'entity',
            'services.digital_sms.sender_id' => 'KEYLNS',
            'services.digital_sms.template_id' => 'template',
        ]);

        $service = new class extends DigitalSmsOtpService {
            public array $payload = [];
            protected function dispatch(array $payload): array
            {
                $this->payload = $payload;
                return ['http_status' => 200, 'body' => '{"status":"success"}'];
            }
        };

        $service->send('9330109091', '5678');

        $this->assertSame('919330109091', $service->payload['recipient']);
    }
}
