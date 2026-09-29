<?php

namespace Tests\Unit;

use App\Services\KeylineSmsService;
use App\Models\GeneralSetting;
use App\Models\User\UserMaster;
use App\Notifications\LoginSMSNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
        $service = new class extends KeylineSmsService {
            public array $payload = [];
            protected function dispatch(array $postData): array
            {
                $this->payload = $postData;
                return ['http_status' => 200, 'body' => '{"status":"success"}'];
            }
        };
        $result = $service->to('9330109091')->line('Your OTP is 1234')->send();

        $this->assertSame('success', $result['status']);
        $this->assertSame('secret-key', $service->payload['apikey']);
        $this->assertSame('NETWORK', $service->payload['senderid']);
        $this->assertSame('9330109091', $service->payload['number']);
        $this->assertSame('json', $service->payload['format']);
    }

    public function test_login_sms_can_be_built_without_a_user_details_record(): void
    {
        $user = UserMaster::create([
            'um_utm_id' => 3,
            'um_user_name' => 'Test Member',
            'um_mobile_no' => '9330109091',
            'um_email_id' => uniqid('sms-').'@example.test',
            'um_status' => 2,
            'um_profile_type' => 'O',
            'um_otp' => '1234',
        ]);

        $message = (new LoginSMSNotification())->toSms($user);

        $this->assertInstanceOf(KeylineSmsService::class, $message);
    }
}
