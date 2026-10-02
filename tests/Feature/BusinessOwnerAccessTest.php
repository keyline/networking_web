<?php

namespace Tests\Feature;

use App\Models\MemberMembership;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserMaster;
use App\Models\User\UserDetails;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusinessOwnerAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_paid_approved_member_cannot_create_another_business(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'multi-owner',
            'um_email_id' => 'multi-owner@example.test', 'um_mobile_no' => '9876500099',
            'um_status' => 2, 'um_profile_type' => 'O',
        ]);
        MemberMembership::create([
            'user_id' => $member->um_id, 'registration_date' => today(),
            'renewal_date' => today()->addYear(), 'payment_date' => today(),
            'payment_amount' => 1000, 'membership_status' => 'active',
        ]);
        $this->actingAs($member, 'member')->get(route('member.businesses.create'))->assertForbidden();
        $this->actingAs($member, 'member')->post(route('member.businesses.store'), [
            'business_name' => 'Member Attempted Business',
        ])->assertForbidden();

        $this->assertCount(0, $member->fresh()->companies);
    }

    public function test_expired_membership_cannot_manage_businesses(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'expired-owner',
            'um_email_id' => 'expired-owner@example.test', 'um_mobile_no' => '9876500098',
            'um_status' => 2, 'um_profile_type' => 'G',
        ]);
        MemberMembership::create([
            'user_id' => $member->um_id, 'registration_date' => today()->subYear(),
            'renewal_date' => today()->subDay(), 'payment_amount' => 1000,
            'membership_status' => 'expired',
        ]);

        $this->actingAs($member, 'member')->get(route('member.businesses.create'))->assertForbidden();
    }

    public function test_linked_business_is_shown_as_an_editable_dashboard_card_without_granting_add_business_access(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'linked-owner',
            'um_email_id' => 'linked-owner@example.test', 'um_mobile_no' => '9876500097',
            'um_status' => 2, 'um_profile_type' => 'G',
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Linked Member Business',
            'public_slug' => 'linked-member-business', 'cmpd_description' => 'A linked business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $member->um_id]);

        $editUrl = route('member.portfolio.edit', $company->portfolioRouteToken());
        $this->actingAs($member, 'member')->get(route('dashboard.index'))
            ->assertOk()->assertSee('My businesses')->assertSee('Linked Member Business')
            ->assertSee('aria-label="Edit Linked Member Business"', false)->assertSee('Edit →');
        $this->actingAs($member, 'member')->get($editUrl)->assertOk();
        $this->actingAs($member, 'member')->get(route('member.businesses.create'))->assertForbidden();
    }

    public function test_business_owner_dashboard_shows_registered_user_interactions(): void
    {
        $owner = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'interaction-owner',
            'um_email_id' => 'interaction-owner@example.test', 'um_mobile_no' => '9876501197', 'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create(['cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Interaction Business', 'cmpd_description' => 'Interaction test business.', 'cmpd_status' => 1]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);

        $guest = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'interaction-guest',
            'um_email_id' => 'interaction-guest@example.test', 'um_mobile_no' => '9876501196', 'um_status' => 2,
        ]);
        UserDetails::create(['ud_um_id' => $guest->um_id, 'ud_first_name' => 'Interested', 'ud_last_name' => 'Guest']);
        DB::table('business_analytics_events')->insert([
            'bae_cmp_id' => $company->cmp_id, 'bae_event_type' => 'whatsapp',
            'bae_um_id' => $guest->um_id, 'bae_source' => 'WEB', 'bae_created_at' => now(),
        ]);

        $this->actingAs($owner, 'member')->get(route('dashboard.index'))
            ->assertOk()->assertSee('Recent business interactions')
            ->assertSee('Interested Guest')->assertSee('Whatsapp')->assertSee('Interaction Business');
    }
}
