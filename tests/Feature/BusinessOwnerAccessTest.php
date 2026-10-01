<?php

namespace Tests\Feature;

use App\Models\Business\BusinessCategoryMaster;
use App\Models\MemberMembership;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusinessOwnerAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_paid_approved_member_can_create_multiple_pending_businesses(): void
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
        $category = BusinessCategoryMaster::where('status', 1)->firstOrFail();

        foreach (['First Member Business', 'Second Member Business'] as $name) {
            $this->actingAs($member, 'member')->post(route('member.businesses.store'), [
                'business_name' => $name,
                'category_ids' => [$category->bcm_id],
            ])->assertRedirect();
        }

        $member->refresh();
        $this->assertCount(2, $member->companies);
        $this->assertSame([0, 0], $member->companies->load('details')->pluck('details.cmpd_status')->map(fn ($status) => (int) $status)->all());
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
}
