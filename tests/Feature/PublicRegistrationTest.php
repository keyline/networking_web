<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business\BusinessCategoryMaster;
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

    public function test_public_registration_creates_an_active_guest_who_can_use_mobile_otp(): void
    {
        PublicRegistrationSetting::current()->update(['enabled' => true]);

        $this->post(route('join.store'), $this->registrationData())
            ->assertRedirect(route('join.create'))
            ->assertSessionHas('registration_success');

        $member = UserMaster::where('um_email_id', 'new-member@example.test')->firstOrFail();
        $this->assertSame(1, (int) $member->um_utm_id);
        $this->assertSame(2, (int) $member->um_status);
        $this->assertCount(0, $member->companies);
        $this->post(route('member.send-mobile-otp'), ['mobile' => $member->um_mobile_no])
            ->assertRedirect(route('member.index'))
            ->assertSessionHas('member_otp_user_id', $member->um_id);
    }

    public function test_public_registration_can_include_a_pending_linked_business(): void
    {
        PublicRegistrationSetting::current()->update(['enabled' => true]);
        $category = BusinessCategoryMaster::where('status', 1)->firstOrFail();

        $this->post(route('join.store'), array_merge($this->registrationData(), [
            'wants_business' => '1',
            'business_name' => 'New Join Business',
            'category_ids' => [$category->bcm_id],
            'business_email' => 'business@example.test',
            'business_phone' => '9876543211',
            'business_description' => 'A business submitted from the public Join form.',
        ]))->assertRedirect(route('join.create'))->assertSessionHas('registration_success');

        $member = UserMaster::where('um_email_id', 'new-member@example.test')->firstOrFail();
        $business = $member->companies()->with('details')->firstOrFail();
        $this->assertSame(1, (int) $member->um_status);
        $this->assertSame('New Join Business', $business->details->cmpd_name);
        $this->assertSame(0, (int) $business->details->cmpd_status);
        $this->assertTrue($business->categories()->where('business_category_master.bcm_id', $category->bcm_id)->exists());

        $admin = Admin::create(['name' => 'Registration Admin', 'email' => uniqid().'@example.test', 'password' => bcrypt('test'), 'type' => 's', 'status' => 1]);
        $this->actingAs($admin, 'admin')->post(route('admin.registrations.approve', $member))->assertSessionHas('success_message');
        $this->assertSame(2, (int) $member->fresh()->um_status);
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
