<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\PublicRegistrationSetting;
use App\Models\Country;
use App\Models\State;
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

    public function test_join_registration_always_creates_a_pending_business_member(): void
    {
        PublicRegistrationSetting::current()->update(['enabled' => true]);
        $category = BusinessCategoryMaster::where('status', 1)->firstOrFail();
        $state = State::query()->firstOrFail();
        $country = Country::findOrFail($state->country_id);

        $this->post(route('join.store'), array_merge($this->registrationData(), [
            'business_name' => 'New Join Business',
            'category_ids' => [$category->bcm_id],
            'business_address_line_1' => '12 Network Avenue',
            'business_address_line_2' => 'Commerce Tower',
            'business_city' => 'Kolkata',
            'business_country' => $country->id,
            'business_state' => $state->id,
            'business_pincode' => '700001',
        ]))
            ->assertRedirect(route('join.create'))
            ->assertSessionHas('registration_success');

        $this->get(route('join.create'))
            ->assertOk()
            ->assertSee('Registration received')
            ->assertSee('Explore members and businesses')
            ->assertSee(route('members.index'), false)
            ->assertDontSee('name="first_name"', false)
            ->assertDontSee('Submit registration');

        $member = UserMaster::where('um_email_id', 'new-member@example.test')->firstOrFail();
        $this->assertSame(2, (int) $member->um_utm_id);
        $this->assertSame(1, (int) $member->um_status);
        $business = $member->companies()->with('details')->firstOrFail();
        $this->assertSame('New Join Business', $business->details->cmpd_name);
        $this->assertSame(0, (int) $business->details->cmpd_status);
        $this->assertSame('12 Network Avenue', $business->details->cmpd_address1);
        $this->assertSame('Commerce Tower', $business->details->cmpd_address2);
        $this->assertSame('Kolkata', $business->details->cmpd_address3);
        $this->assertSame($country->id, (int) $business->details->cmpd_country);
        $this->assertSame($state->id, (int) $business->details->cmpd_state);
        $this->assertSame('700001', $business->details->cmpd_pincode);
        $this->assertSame('Dr.', $member->userDetail->ud_salutation);
        $this->assertNull($member->userDetail->ud_addr_1);
        $this->assertNull($member->userDetail->ud_addr_2);
        $this->assertNull($member->userDetail->ud_pincode);

        $admin = Admin::query()->firstOrFail();
        $this->actingAs($admin, 'admin')->post(route('admin.registrations.approve-member', $member))->assertRedirect();
        $this->assertSame(2, (int) $member->fresh()->um_status);
        $this->assertSame(0, (int) $business->details->fresh()->cmpd_status);
        $this->post(route('admin.registrations.approve-business', [$member, $business]))->assertRedirect();
        $this->assertSame(1, (int) $business->details->fresh()->cmpd_status);
    }

    public function test_guest_verifies_mobile_before_completing_required_profile(): void
    {
        $state = State::query()->firstOrFail();
        $country = Country::findOrFail($state->country_id);

        $this->post(route('guest.register.complete'), [])->assertForbidden();
        $this->post(route('guest.register.send-otp'), ['mobile' => '9876543299'])
            ->assertRedirect(route('guest.register'));
        $otp = session('testing_guest_registration_otp');
        $this->post(route('guest.register.verify-otp'), ['otp' => $otp])
            ->assertRedirect(route('guest.register'));
        $this->post(route('guest.register.complete'), [
            'salutation' => 'Ms.', 'first_name' => 'Guest', 'last_name' => 'Viewer', 'email' => 'guest-viewer@example.test',
            'country' => $country->id, 'state' => $state->id, 'consent' => '1',
        ])->assertRedirect();

        $guest = UserMaster::where('um_email_id', 'guest-viewer@example.test')->firstOrFail();
        $this->assertSame(1, (int) $guest->um_utm_id);
        $this->assertSame(2, (int) $guest->um_status);
        $this->assertSame('9876543299', $guest->um_mobile_no);
        $this->assertSame('Ms.', $guest->userDetail->ud_salutation);
        $this->assertNull($guest->userDetail->ud_addr_1);
        $this->assertNull($guest->userDetail->ud_addr_2);
        $this->assertNull($guest->userDetail->ud_pincode);
        $this->assertAuthenticatedAs($guest, 'member');
    }

    public function test_join_form_labels_address_as_business_information_only(): void
    {
        PublicRegistrationSetting::current()->update(['enabled' => true]);

        $this->get(route('join.create'))
            ->assertOk()
            ->assertSee('Business address')
            ->assertSee('name="salutation"', false)
            ->assertSee('Prof.')
            ->assertSee('name="business_address_line_1"', false)
            ->assertSee('name="business_country"', false)
            ->assertDontSee('name="address_line_1"', false);
    }

    private function registrationData(): array
    {
        return [
            'salutation' => 'Dr.', 'first_name' => 'New', 'last_name' => 'Member',
            'email' => 'new-member@example.test', 'mobile' => '9876543210',
            'whatsapp' => '9876543210', 'consent' => '1',
        ];
    }
}
