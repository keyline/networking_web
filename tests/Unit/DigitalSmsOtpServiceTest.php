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
        $this->assertStringContainsString('1234', $service->payload['message']);
    }
}
