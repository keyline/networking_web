<?php

namespace Tests\Feature;

use App\Models\User\UserMaster;
use App\Notifications\UserOtpEmailNotify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MemberEmailOtpLoginTest extends TestCase
{
    use DatabaseTransactions;

    public function test_active_guest_can_request_and_verify_an_email_otp(): void
    {
        Notification::fake();

        $user = UserMaster::create([
            'um_utm_id' => 3,
            'um_user_name' => 'otp-guest-' . uniqid(),
            'um_mobile_no' => '9' . random_int(100000000, 999999999),
            'um_email_id' => uniqid('otp-guest-') . '@example.test',
            'um_status' => 2,
            'um_profile_type' => 'O',
        ]);

        $this->post('/member/send-otp', ['email' => $user->um_email_id])
            ->assertRedirect(route('member.index'))
            ->assertSessionHas('member_otp_email', $user->um_email_id);

        Notification::assertSentTo($user, UserOtpEmailNotify::class);
        $otp = $user->fresh()->um_otp;

        $this->withSession(['member_otp_email' => $user->um_email_id])
            ->post('/member/verify-otp', [
                'email' => $user->um_email_id,
                'otp' => $otp,
            ])
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticatedAs($user, 'member');
        $this->assertNull($user->fresh()->um_otp_secret);
    }

    public function test_inactive_member_cannot_request_an_otp(): void
    {
        Notification::fake();

        $user = UserMaster::create([
            'um_utm_id' => 3,
            'um_user_name' => 'inactive-' . uniqid(),
            'um_mobile_no' => '8' . random_int(100000000, 999999999),
            'um_email_id' => uniqid('inactive-') . '@example.test',
            'um_status' => 1,
            'um_profile_type' => 'O',
        ]);

        $this->from('/member')
            ->post('/member/send-otp', ['email' => $user->um_email_id])
            ->assertRedirect(route('member.index'))
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }
}
