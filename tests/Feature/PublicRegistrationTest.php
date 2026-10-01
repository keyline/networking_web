<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\Country;
use App\Models\State;
use App\Models\User\UserMaster;
use App\Notifications\UserOtpEmailNotify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_registration_creates_pending_member_and_business(): void
    {
        $categories = BusinessCategoryMaster::where('status', 1)->limit(2)->get();
        $this->assertCount(2, $categories);
        $country = Country::where('name', 'India')->firstOrFail();
        $state = State::where('country_id', $country->id)->firstOrFail();

        $response = $this->post(route('join.store'), [
            'first_name' => 'Test',
            'last_name' => 'Owner',
            'email' => 'new-owner@example.test',
            'mobile' => '9876543210',
            'whatsapp' => '9876543210',
            'business_name' => 'Fresh Start Services',
            'business_categories' => $categories->pluck('bcm_id')->all(),
            'description' => 'Professional business services.',
            'address_line_1' => '1 Business Street',
            'city' => 'Kolkata',
            'country' => $country->id,
            'state' => $state->id,
            'pincode' => '700001',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('join.create'));
        $response->assertSessionHas('registration_success');
        $this->assertDatabaseHas('user_master', [
            'um_email_id' => 'new-owner@example.test',
            'um_mobile_no' => '9876543210',
            'um_status' => 1,
        ]);
        $this->assertDatabaseHas('companies_details', [
            'cmpd_name' => 'Fresh Start Services',
            'cmpd_country' => $country->id,
            'cmpd_state' => $state->id,
            'cmpd_status' => 0,
        ]);
        $this->assertDatabaseHas('categories_to_companies', [
            'ctc_bcm_id' => $categories[0]->bcm_id,
        ]);
        $this->assertDatabaseHas('categories_to_companies', ['ctc_bcm_id' => $categories[1]->bcm_id]);
    }

    public function test_admin_approval_unlocks_login_and_owned_business_editor(): void
    {
        Notification::fake();
        $category = BusinessCategoryMaster::where('status', 1)->firstOrFail();
        $country = Country::where('name', 'India')->firstOrFail();
        $state = State::where('country_id', $country->id)->firstOrFail();
        $email = uniqid('approval-').'@example.test';
        $mobile = '9'.random_int(100000000, 999999999);

        $this->post(route('join.store'), [
            'first_name' => 'Approval',
            'last_name' => 'Flow',
            'email' => $email,
            'mobile' => $mobile,
            'business_name' => 'Approval Flow Business',
            'business_categories' => [$category->bcm_id],
            'address_line_1' => '10 Test Street',
            'city' => 'Kolkata',
            'country' => $country->id,
            'state' => $state->id,
            'pincode' => '700001',
            'consent' => '1',
        ])->assertSessionHas('registration_success');

        $member = UserMaster::where('um_email_id', $email)->firstOrFail();
        $company = $member->companies()->with('details')->firstOrFail();
        $this->assertSame(1, (int) $member->um_status);
        $this->assertSame(0, (int) $company->details->cmpd_status);

        $this->post(route('member.send-otp'), ['email' => $email])
            ->assertSessionHasErrors('email');
        Notification::assertNothingSent();

        $admin = Admin::create([
            'name' => 'Approval administrator',
            'email' => uniqid('approval-admin-').'@example.test',
            'password' => bcrypt('test-password'),
            'type' => 'ma',
            'status' => 1,
        ]);
        $this->actingAs($admin, 'admin')
            ->post(route('admin.registrations.approve', $member))
            ->assertSessionHas('success_message');

        $this->assertDatabaseHas('user_master', ['um_id' => $member->um_id, 'um_status' => 2]);
        $this->assertDatabaseHas('companies_details', [
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_status' => 1,
            'cmpd_is_document_valid' => '1',
        ]);

        auth('admin')->logout();
        $this->post(route('member.send-otp'), ['email' => $email])
            ->assertRedirect(route('member.index'));
        Notification::assertSentTo($member, UserOtpEmailNotify::class);

        $otp = $member->fresh()->um_otp;
        $this->withSession(['member_otp_email' => $email])
            ->post(route('member.verify-otp'), ['email' => $email, 'otp' => $otp])
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticatedAs($member, 'member');
        $this->get(route('member.portfolio.edit', $company->portfolioRouteToken()))
            ->assertOk()
            ->assertSeeText('Business identity');

        $member->update(['um_status' => 0]);
        $this->get(route('dashboard.index'))
            ->assertRedirect(route('member.index'))
            ->assertSessionHasErrors('account');
        $this->assertGuest('member');
    }
}
