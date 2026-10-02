<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\State;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GuestRegistrationApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mobile_guest_verifies_phone_before_submitting_profile(): void
    {
        $state = State::query()->firstOrFail();
        $country = Country::findOrFail($state->country_id);
        $headers = ['key' => (string) env('PROJECT_KEY')];

        $send = $this->withHeaders($headers)->postJson('/api/v1/auth/guest/otp/send', [
            'mobile_no' => '9876507788',
        ])->assertOk()->assertJson(['status' => true]);

        $otp = $send->json('data.testing_otp');
        $verify = $this->withHeaders($headers)->postJson('/api/v1/auth/guest/otp/verify', [
            'mobile_no' => '9876507788', 'otp' => $otp,
        ])->assertOk()->assertJson(['status' => true]);

        $this->withHeaders($headers)->postJson('/api/v1/auth/guest/register', [
            'verification_token' => $verify->json('data.verification_token'),
            'salutation' => 'Prof.', 'first_name' => 'Mobile', 'last_name' => 'Guest',
            'email' => 'mobile-guest@example.test',
            'country_id' => $country->id, 'state_id' => $state->id,
        ])->assertOk()->assertJson(['status' => true, 'data' => ['user_type_id' => 1]]);

        $guest = UserMaster::where('um_email_id', 'mobile-guest@example.test')->firstOrFail();
        $this->assertSame(2, (int) $guest->um_status);
        $this->assertSame('9876507788', $guest->um_mobile_no);
        $this->assertSame('Prof.', $guest->userDetail->ud_salutation);
        $this->assertNull($guest->userDetail->ud_addr_1);
        $this->assertNull($guest->userDetail->ud_addr_2);
        $this->assertNull($guest->userDetail->ud_pincode);
        $this->assertCount(0, $guest->companies);
    }
}
