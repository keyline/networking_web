<?php

namespace Tests\Feature;

use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\Review\ReviewMaster;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MemberDashboardReviewTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_relabels_reference_action_and_allows_a_user_to_review_another_business(): void
    {
        $reviewer = UserMaster::create([
            'um_utm_id' => 1,
            'um_user_name' => 'reviewer',
            'um_email_id' => 'reviewer@example.test',
            'um_mobile_no' => '9100000201',
            'um_status' => 2,
        ]);
        [$owner, $business] = $this->businessMember('review-target', '9100000202');

        $this->actingAs($reviewer, 'member')->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('data-open="reviewModal">Write a review</button>', false)
            ->assertSee('data-open="referralModal">Share a reference</button>', false)
            ->assertSee('Review Target Business')
            ->assertDontSee('data-open="enquiryModal">Post an enquiry</button>', false)
            ->assertDontSee('aria-label="Quick actions"', false)
            ->assertDontSee('Ask the network');

        $this->actingAs($reviewer, 'member')->post(route('member.reviews.store'), [
            'company_id' => $business->cmp_id,
            'rating' => 5,
            'comment' => 'Helpful service and an excellent response.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'rev_cmp_id' => $business->cmp_id,
            'rev_um_id' => $reviewer->um_id,
            'rev_rating' => 5,
            'rev_comment' => 'Helpful service and an excellent response.',
            'status' => 1,
        ]);
    }

    public function test_user_cannot_review_an_owned_business_or_review_the_same_business_twice(): void
    {
        [$reviewer, $ownedBusiness] = $this->businessMember('review-owner', '9100000203');
        [, $otherBusiness] = $this->businessMember('review-other', '9100000204');

        $this->actingAs($reviewer, 'member')->post(route('member.reviews.store'), [
            'company_id' => $ownedBusiness->cmp_id,
            'rating' => 4,
            'comment' => 'An invalid self review.',
        ])->assertRedirect()->assertSessionHasErrors('company_id')->assertSessionHas('open_dialog', 'reviewModal');

        ReviewMaster::create([
            'rev_cmp_id' => $otherBusiness->cmp_id,
            'rev_um_id' => $reviewer->um_id,
            'rev_rating' => 4,
            'rev_comment' => 'Existing review.',
            'status' => 1,
        ]);

        $this->actingAs($reviewer, 'member')->post(route('member.reviews.store'), [
            'company_id' => $otherBusiness->cmp_id,
            'rating' => 5,
            'comment' => 'A duplicate review.',
        ])->assertRedirect()->assertSessionHasErrors('company_id')->assertSessionHas('open_dialog', 'reviewModal');

        $this->assertSame(1, ReviewMaster::where('rev_um_id', $reviewer->um_id)->count());
    }

    private function businessMember(string $username, string $mobile): array
    {
        $member = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => $username,
            'um_email_id' => $username.'@example.test',
            'um_mobile_no' => $mobile,
            'um_status' => 2,
        ]);
        $business = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $business->cmp_id,
            'cmpd_name' => str($username)->replace('-', ' ')->title()->append(' Business')->toString(),
            'cmpd_description' => 'Review test business.',
            'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert([
            'ucm_cmp_id' => $business->cmp_id,
            'ucm_um_id' => $member->um_id,
        ]);

        return [$member, $business];
    }
}
