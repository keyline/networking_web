<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PublicRegistrationSetting;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_join_link_can_be_closed_by_an_administrator(): void
    {
        PublicRegistrationSetting::current()->update(['enabled' => false]);

        $this->get(route('join.create'))->assertOk()->assertSeeText('Registration is currently closed');
        $this->post(route('join.store'), $this->registrationData())->assertForbidden();
    }

    public function test_public_registration_creates_only_a_pending_normal_member(): void
    {
        PublicRegistrationSetting::current()->update(['enabled' => true]);

        $this->post(route('join.store'), $this->registrationData())
            ->assertRedirect(route('join.create'))
            ->assertSessionHas('registration_success');

        $member = UserMaster::where('um_email_id', 'new-member@example.test')->firstOrFail();
        $this->assertSame(1, (int) $member->um_utm_id);
        $this->assertSame(1, (int) $member->um_status);
        $this->assertCount(0, $member->companies);
    }

    public function test_admin_approval_unlocks_normal_member_login_without_business_access(): void
    {
        PublicRegistrationSetting::current()->update(['enabled' => true]);
        $this->post(route('join.store'), $this->registrationData())->assertSessionHas('registration_success');
        $member = UserMaster::where('um_email_id', 'new-member@example.test')->firstOrFail();
        $admin = Admin::create(['name' => 'Registration Admin', 'email' => uniqid().'@example.test', 'password' => bcrypt('test'), 'type' => 's', 'status' => 1]);

        $this->actingAs($admin, 'admin')->post(route('admin.registrations.approve', $member))->assertSessionHas('success_message');

        $this->assertSame(2, (int) $member->fresh()->um_status);
        $this->actingAs($member->fresh(), 'member')->get(route('dashboard.index'))->assertOk();
        $this->get(route('member.businesses.create'))->assertForbidden();
    }

    private function registrationData(): array
    {
        return [
            'first_name' => 'New', 'last_name' => 'Member',
            'email' => 'new-member@example.test', 'mobile' => '9876543210',
            'whatsapp' => '9876543210', 'consent' => '1',
        ];
    }
}
