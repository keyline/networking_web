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
        UserDetails::create([
            'ud_um_id' => $member->um_id,
            'ud_first_name' => 'Linked',
            'ud_last_name' => 'Owner',
            'ud_profile_image' => 'linked-owner-photo.jpg',
        ]);

        $editUrl = route('member.portfolio.edit', $company->portfolioRouteToken());
        $this->actingAs($member, 'member')->get(route('dashboard.index'))
            ->assertOk()->assertSee('Edit my business')->assertSee('Linked Member Business')
            ->assertSee('<a class="brand" href="'.url('/').'">', false)
            ->assertSee('aria-label="Edit Linked Member Business"', false)->assertSee('Edit →')
            ->assertSee('class="profile-photo"', false)
            ->assertSee(env('UPLOADS_URL').'user/linked-owner-photo.jpg', false)
            ->assertSee('Linked Owner profile photo')
            ->assertSee('business-priority')->assertSeeInOrder(['Edit my business', 'My profile']);
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

    public function test_business_owner_is_sent_from_home_to_dashboard_with_page_analytics(): void
    {
        $owner = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'analytics-owner',
            'um_email_id' => 'analytics-owner@example.test', 'um_mobile_no' => '9876502297', 'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Analytics Business',
            'cmpd_description' => 'Analytics test business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);
        DB::table('business_analytics_events')->insert([
            [
                'bae_cmp_id' => $company->cmp_id, 'bae_event_type' => 'view',
                'bae_device_id' => 'visitor-one', 'bae_source' => 'WEB', 'bae_created_at' => now(),
            ],
            [
                'bae_cmp_id' => $company->cmp_id, 'bae_event_type' => 'view',
                'bae_device_id' => 'visitor-two', 'bae_source' => 'WEB', 'bae_created_at' => now(),
            ],
            [
                'bae_cmp_id' => $company->cmp_id, 'bae_event_type' => 'whatsapp',
                'bae_device_id' => 'visitor-two', 'bae_source' => 'WEB', 'bae_created_at' => now(),
            ],
        ]);

        $this->actingAs($owner, 'member')->get('/')
            ->assertRedirect(route('dashboard.index'));
        $this->actingAs($owner, 'member')->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('aria-label="Quick actions"', false)
            ->assertSee('Your page analytics')
            ->assertSee('Last 30 days')
            ->assertSeeInOrder(['2', 'Page views', '2', 'Unique visitors', '1', 'Contact clicks', '0', 'Enquiries']);
    }

    public function test_logged_in_guest_can_still_view_homepage(): void
    {
        $guest = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'homepage-guest',
            'um_email_id' => 'homepage-guest@example.test', 'um_mobile_no' => '9876502296', 'um_status' => 2,
        ]);

        $this->actingAs($guest, 'member')->get('/')->assertOk();
    }

    public function test_active_member_can_search_the_authenticated_member_directory(): void
    {
        $viewer = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'directory-viewer',
            'um_email_id' => 'directory-viewer@example.test', 'um_mobile_no' => '9876502391', 'um_status' => 2,
        ]);
        $listed = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'directory-member',
            'um_email_id' => 'directory-member@example.test', 'um_mobile_no' => '9876502392', 'um_status' => 2,
        ]);
        UserDetails::create(['ud_um_id' => $listed->um_id, 'ud_first_name' => 'Asha', 'ud_last_name' => 'Sen']);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Compact Design Studio',
            'cmpd_description' => 'Directory test business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $listed->um_id]);
        DB::table('member_meetings')->insert([
            'reported_by_um_id' => $viewer->um_id, 'counterpart_um_id' => $listed->um_id,
            'invited_by_um_id' => $listed->um_id, 'meeting_at' => now(), 'mode' => 'online',
            'details' => 'Directory activity test.', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $reference = \App\Models\Enquiries\EnquiryMaster::create([
            'enm_name' => 'Referral contact', 'enm_subject' => 'Member referral',
            'enm_description' => 'Directory activity test.', 'enm_type' => 2,
            'enm_is_myself' => 0, 'enm_status' => 1,
        ]);
        DB::table('enquiry_to_user')->insert([
            'etu_cmp_id' => 0, 'etu_enm_id' => $reference->enm_id,
            'etu_um_id' => $listed->um_id, 'etu_created_at' => now(),
        ]);
        DB::table('business_analytics_events')->insert([
            'bae_cmp_id' => $company->cmp_id, 'bae_event_type' => 'view',
            'bae_um_id' => $listed->um_id, 'bae_source' => 'WEB', 'bae_created_at' => now(),
        ]);
        \App\Models\Review\ReviewMaster::create([
            'rev_um_id' => $listed->um_id, 'rev_cmp_id' => $company->cmp_id,
            'rev_rating' => 5, 'rev_comment' => 'Excellent collaboration.', 'status' => 1,
        ]);

        $this->get(route('member.members.index'))->assertRedirect();
        $this->actingAs($viewer, 'member')
            ->get(route('member.members.index', ['search' => 'Compact Design']))
            ->assertOk()
            ->assertSee('Members')
            ->assertSee('Asha Sen')
            ->assertSee('9876502392')
            ->assertSee('Compact Design Studio')
            ->assertSee('View activity')
            ->assertSee('Last 30 days')
            ->assertSee('All time')
            ->assertSee('Reviews')
            ->assertSee('"recent":{"meetings":1,"references":1,"activity":1,"reviews":1}', false);
    }
}
