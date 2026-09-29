<?php

namespace Tests\Unit;

use App\Services\KeylineSmsService;
use App\Models\GeneralSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KeylineSmsServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_uses_the_same_database_gateway_settings_as_admin_sms(): void
    {
        GeneralSetting::query()->where('id', 1)->update([
            'sms_base_url' => 'https://sms.example.test/send',
            'sms_sender_id' => 'NETWORK',
            'sms_authentication_key' => 'secret-key',
        ]);
        Http::fake(['sms.example.test/*' => Http::response(['status' => 'success'], 200)]);

        $result = (new KeylineSmsService())->to('9330109091')->line('Your OTP is 1234')->send();

        $this->assertSame('success', $result['status']);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request['apikey'] === 'secret-key'
            && $request['senderid'] === 'NETWORK'
            && $request['number'] === '9330109091'
            && $request['format'] === 'json'
        );
    }
}
