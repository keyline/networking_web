<?php

namespace Tests\Feature;

use App\Models\Business\BusinessCategoryMaster;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\Enquiries\EnquiryMaster;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MemberReferenceAudienceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reference_can_be_shared_with_all_members_or_an_individual_business(): void
    {
        $sender = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'reference-sender',
            'um_email_id' => 'reference-sender@example.test', 'um_mobile_no' => '9100000301', 'um_status' => 2,
        ]);
        $owner = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'reference-owner',
            'um_email_id' => 'reference-owner@example.test', 'um_mobile_no' => '9100000306', 'um_status' => 2,
        ]);
        $business = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $business->cmp_id, 'cmpd_name' => 'Reference Target Business',
            'cmpd_description' => 'Reference audience test business.', 'cmpd_status' => 1,
        ]);
        $firstCategory = BusinessCategoryMaster::create(['name' => 'Reference Advisory']);
        $secondCategory = BusinessCategoryMaster::create(['name' => 'Reference Logistics']);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $business->cmp_id, 'ucm_um_id' => $owner->um_id]);
        DB::table('categories_to_companies')->insert([
            ['ctc_cmp_id' => $business->cmp_id, 'ctc_bcm_id' => $firstCategory->bcm_id, 'ctc_created_at' => now()],
            ['ctc_cmp_id' => $business->cmp_id, 'ctc_bcm_id' => $secondCategory->bcm_id, 'ctc_created_at' => now()],
        ]);

        $this->actingAs($sender, 'member')->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Who should receive this reference?')
            ->assertSee('All members')
            ->assertSee('An individual business')
            ->assertSee('Reference Advisory, Reference Logistics');

        $this->actingAs($sender, 'member')->post(route('member.referrals.store'), [
            'audience' => 'all_members',
            'name' => 'All Member Lead',
            'phone' => '9100000302',
            'note' => 'A useful connection for all members.',
        ])->assertRedirect()->assertSessionHas('success');

        $allMembersReference = EnquiryMaster::where('enm_description', 'A useful connection for all members.')->firstOrFail();
        $this->assertSame(2, (int) $allMembersReference->enm_type);
        $this->assertDatabaseHas('enquiry_to_user', [
            'etu_enm_id' => $allMembersReference->enm_id,
            'etu_um_id' => $sender->um_id,
            'etu_cmp_id' => 0,
        ]);

        $this->actingAs($sender, 'member')->post(route('member.referrals.store'), [
            'audience' => 'individual',
            'referral_company_id' => $business->cmp_id,
            'name' => 'Individual Lead',
            'phone' => '9100000303',
            'note' => 'A useful connection for one business.',
        ])->assertRedirect()->assertSessionHas('success');

        $individualReference = EnquiryMaster::where('enm_description', 'A useful connection for one business.')->firstOrFail();
        $this->assertSame(1, (int) $individualReference->enm_type);
        $this->assertDatabaseHas('enquiry_to_user', [
            'etu_enm_id' => $individualReference->enm_id,
            'etu_um_id' => $sender->um_id,
            'etu_cmp_id' => $business->cmp_id,
        ]);
    }

    public function test_individual_reference_requires_a_business(): void
    {
        $sender = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'reference-validator',
            'um_email_id' => 'reference-validator@example.test', 'um_mobile_no' => '9100000304', 'um_status' => 2,
        ]);

        $this->actingAs($sender, 'member')->post(route('member.referrals.store'), [
            'audience' => 'individual',
            'name' => 'Missing Business',
            'phone' => '9100000305',
            'note' => 'This reference should not be stored.',
        ])->assertRedirect()
            ->assertSessionHasErrors('referral_company_id')
            ->assertSessionHas('open_dialog', 'referralModal');

        $this->assertDatabaseMissing('enquiry_master', [
            'enm_description' => 'This reference should not be stored.',
        ]);
    }
}
