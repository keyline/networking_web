<?php

namespace Tests\Feature;

use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberProfileTest extends TestCase
{
    use DatabaseTransactions;

    private function member(): UserMaster
    {
        $member = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'profile-'.uniqid(),
            'um_mobile_no' => '9876501234',
            'um_email_id' => uniqid('profile-').'@example.test',
            'um_password' => Hash::make('current-password'),
            'um_status' => 2,
            'um_profile_type' => 'O',
        ]);
        UserDetails::create(['ud_um_id' => $member->um_id, 'ud_first_name' => 'Old', 'ud_last_name' => 'Name']);

        return $member;
    }

    public function test_member_can_open_and_update_their_profile(): void
    {
        $member = $this->member();
        $this->actingAs($member, 'member')->get(route('member.profile.show'))
            ->assertOk()->assertSee('My profile')->assertSee($member->um_email_id);

        $this->actingAs($member, 'member')->put(route('member.profile.update'), [
            'first_name' => 'Subrata',
            'last_name' => 'Kundu',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame('Subrata', $member->userDetail()->first()->ud_first_name);
        $this->assertSame('Kundu', $member->userDetail()->first()->ud_last_name);
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $member = $this->member();
        $route = route('member.profile.password');
        $this->actingAs($member, 'member')->put($route, [
            'current_password' => 'wrong-password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($member, 'member')->put($route, [
            'current_password' => 'current-password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHas('success');
        $this->assertTrue(Hash::check('new-password', $member->fresh()->um_password));
    }

    public function test_email_and_phone_changes_apply_only_after_otp_verification(): void
    {
        $member = $this->member();

        $this->actingAs($member, 'member')->post(route('member.profile.contact.send-otp'), [
            'contact_type' => 'email', 'contact_value' => 'new-email@example.test',
        ])->assertSessionHas('member_profile_contact_change', ['type' => 'email', 'value' => 'new-email@example.test']);
        $this->assertNotSame('new-email@example.test', $member->fresh()->um_email_id);
        $emailOtp = (string) $member->fresh()->um_otp;
        $this->post(route('member.profile.contact.verify-otp'), ['otp' => $emailOtp])->assertSessionHas('success');
        $this->assertSame('new-email@example.test', $member->fresh()->um_email_id);

        $this->post(route('member.profile.contact.send-otp'), [
            'contact_type' => 'phone', 'contact_value' => '9876509876',
        ])->assertSessionHas('member_profile_contact_change', ['type' => 'phone', 'value' => '9876509876']);
        $this->assertNotSame('9876509876', $member->fresh()->um_mobile_no);
        $phoneOtp = (string) $member->fresh()->um_otp;
        $this->post(route('member.profile.contact.verify-otp'), ['otp' => $phoneOtp])->assertSessionHas('success');
        $this->assertSame('9876509876', $member->fresh()->um_mobile_no);
    }
}
