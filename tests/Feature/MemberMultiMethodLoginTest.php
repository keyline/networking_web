<?php

namespace Tests\Feature;

use App\Models\User\UserMaster;
use App\Services\Member\GoogleMemberAuthService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class MemberMultiMethodLoginTest extends TestCase
{
    use DatabaseTransactions;

    private function activeUser(array $overrides = []): UserMaster
    {
        return UserMaster::create(array_merge([
            'um_utm_id' => 3,
            'um_user_name' => 'login-'.uniqid(),
            'um_mobile_no' => '7'.random_int(100000000, 999999999),
            'um_email_id' => uniqid('login-').'@example.test',
            'um_password' => Hash::make('secret-password'),
            'um_status' => 2,
            'um_profile_type' => 'O',
        ], $overrides));
    }

    public function test_active_member_can_login_with_email_and_password(): void
    {
        $user = $this->activeUser();
        $this->post(route('member.login-password'), ['email' => $user->um_email_id, 'password' => 'secret-password'])
            ->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($user, 'member');
    }

    public function test_member_can_request_and_verify_mobile_otp(): void
    {
        $user = $this->activeUser();
        $response = $this->post(route('member.send-mobile-otp'), ['mobile' => $user->um_mobile_no]);
        $response->assertRedirect(route('member.index'))->assertSessionHas('member_otp_user_id', $user->um_id);
        $otp = $user->fresh()->um_otp;
        $this->post(route('member.verify-mobile-otp'), ['user_id' => $user->um_id, 'otp' => $otp])
            ->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($user, 'member');
    }

    public function test_google_callback_logs_in_matching_active_member(): void
    {
        $user = $this->activeUser();
        $google = Mockery::mock(GoogleMemberAuthService::class);
        $google->shouldReceive('profileFromCode')->once()->with('valid-code')->andReturn([
            'email' => $user->um_email_id,
            'email_verified' => true,
            'sub' => 'google-user-123',
        ]);
        $this->app->instance(GoogleMemberAuthService::class, $google);

        $this->withSession(['member_google_state' => 'safe-state'])
            ->get(route('member.google.callback', ['state' => 'safe-state', 'code' => 'valid-code']))
            ->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($user, 'member');
        $this->assertSame('G', $user->fresh()->um_profile_type);
    }

    public function test_google_callback_rejects_invalid_state(): void
    {
        $this->withSession(['member_google_state' => 'expected'])
            ->get(route('member.google.callback', ['state' => 'wrong', 'code' => 'anything']))
            ->assertRedirect(route('member.index'))
            ->assertSessionHasErrors('google');
        $this->assertGuest('member');
    }

    public function test_inactive_member_cannot_use_password_or_mobile_otp(): void
    {
        $user = $this->activeUser(['um_status' => 1]);
        $this->post(route('member.login-password'), ['email' => $user->um_email_id, 'password' => 'secret-password'])
            ->assertSessionHasErrors('email');
        $this->post(route('member.send-mobile-otp'), ['mobile' => $user->um_mobile_no])
            ->assertSessionHasErrors('mobile');
        $this->assertGuest('member');
    }

    public function test_google_redirect_contains_state_and_configured_callback(): void
    {
        config(['services.google.client_id' => 'client-id', 'services.google.client_secret' => 'client-secret']);
        $response = $this->get(route('member.google.redirect'));
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/', $location);
        $this->assertStringContainsString(urlencode(route('member.google.callback')), $location);
        $this->assertNotEmpty(session('member_google_state'));
    }
}
