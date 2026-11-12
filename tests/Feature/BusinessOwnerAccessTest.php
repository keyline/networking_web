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

    public function test_linked_businesses_are_managed_from_the_account_menu_and_business_listing(): void
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
        $secondCompany = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $secondCompany->cmp_id, 'cmpd_name' => 'Second Member Business',
            'public_slug' => 'second-member-business', 'cmpd_description' => 'Another linked business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $secondCompany->cmp_id, 'ucm_um_id' => $member->um_id]);
        UserDetails::create([
            'ud_um_id' => $member->um_id,
            'ud_first_name' => 'Linked',
            'ud_last_name' => 'Owner',
            'ud_profile_image' => 'linked-owner-photo.jpg',
        ]);

        $editUrl = route('member.portfolio.edit', $company->portfolioRouteToken());
        $this->actingAs($member, 'member')->get(route('dashboard.index'))
            ->assertOk()->assertSee('Edit my business')->assertSee(route('member.businesses.index'))
            ->assertSee('Business activity')->assertSee(route('member.business-activity.index'))
            ->assertSee('Members')->assertSee(route('member.members.index'))
            ->assertSee('Businesses')->assertSee(route('members.index'))
            ->assertDontSee('Businesses to explore')
            ->assertSee('<a class="brand" href="'.url('/').'">', false)
            ->assertDontSee('id="my-business"', false)->assertDontSee('<h2>My profile</h2>', false);
        $this->actingAs($member, 'member')->get(route('member.profile.show'))
            ->assertOk()->assertSee('profile-overview')->assertSee('Linked Owner')
            ->assertSee(env('UPLOADS_URL').'user/linked-owner-photo.jpg', false);
        $this->actingAs($member, 'member')->get(route('member.businesses.index'))
            ->assertOk()->assertSee('Linked Member Business')->assertSee('Second Member Business')
            ->assertSee('href="'.url('/member/businesses/'), false)->assertSee('Edit business →');
        $this->actingAs($member, 'member')->get($editUrl)->assertOk();
        $this->actingAs($member, 'member')->get(route('member.businesses.create'))->assertForbidden();
    }

    public function test_business_owner_activity_page_shows_registered_user_interactions(): void
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
        DB::table('business_analytics_events')->insert([
            'bae_cmp_id' => $company->cmp_id, 'bae_event_type' => 'view',
            'bae_um_id' => null, 'bae_source' => 'WEB', 'bae_created_at' => now(),
        ]);

        $this->actingAs($owner, 'member')->get(route('dashboard.index'))
            ->assertOk()->assertDontSee('Recent business interactions');
        $this->actingAs($owner, 'member')->get(route('member.business-activity.index'))
            ->assertOk()->assertSee('Business activity')
            ->assertSee('Interested Guest')->assertSee('Unregistered visitor')
            ->assertSee('WhatsApp')->assertSee('Interaction Business');
    }

    public function test_business_owner_can_reply_to_and_share_a_member_enquiry_on_whatsapp(): void
    {
        $owner = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'whatsapp-owner',
            'um_email_id' => 'whatsapp-owner@example.test', 'um_mobile_no' => '9876501195', 'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'WhatsApp Business',
            'cmpd_description' => 'WhatsApp test business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);
        \App\Models\Enquiries\EnquiryMaster::create([
            'enm_name' => 'Asha Sen', 'enm_email' => 'asha@example.test', 'enm_phone' => '9876543210',
            'enm_subject' => 'Need a photographer', 'enm_description' => 'Looking for an event photographer.',
            'enm_type' => 2, 'enm_is_myself' => 0, 'enm_status' => 1,
        ]);

        $this->actingAs($owner, 'member')->get(route('dashboard.index'))
            ->assertOk()->assertSee('Need a photographer')->assertSee('WhatsApp')
            ->assertSee('Share on WhatsApp')->assertSee('https://wa.me/919876543210?text=', false)
            ->assertSee('Asha%20Sen%20has%20posted%20a%20requirement%20in%20our%20Networx%20Business%20Platform%20group', false)
            ->assertSee('Need%20a%20photographer', false)->assertSee('Phone%3A%209876543210', false);
    }

    public function test_business_owner_dashboard_counts_references_the_member_has_given(): void
    {
        $owner = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'reference-count-owner',
            'um_email_id' => 'reference-count@example.test', 'um_mobile_no' => '9876501194', 'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Reference Count Business',
            'cmpd_description' => 'Reference count test business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);

        $this->actingAs($owner, 'member')->post(route('member.referrals.store'), [
            'audience' => 'all_members', 'name' => 'Useful Contact',
            'phone' => '9876543211', 'note' => 'A useful business reference.',
        ])->assertRedirect()->assertSessionHas('success');

        DB::table('enquiry_to_user')->where('etu_um_id', $owner->um_id)->update(['etu_created_at' => now()->subYear()]);

        $this->actingAs($owner, 'member')->get(route('dashboard.index', ['period' => 'last_30']))
            ->assertOk()
            ->assertSee('aria-label="1 references given"', false)
            ->assertDontSee('analytics-stat stat-reference', false);
    }

    public function test_business_owner_dashboard_counts_all_completed_meetings(): void
    {
        $owner = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'meeting-count-owner',
            'um_email_id' => 'meeting-count@example.test', 'um_mobile_no' => '9876501195', 'um_status' => 2,
        ]);
        $otherMember = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'meeting-count-member',
            'um_email_id' => 'meeting-member@example.test', 'um_mobile_no' => '9876501196', 'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Meeting Count Business',
            'cmpd_description' => 'Meeting count test business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);
        DB::table('member_meetings')->insert([
            'reported_by_um_id' => $owner->um_id, 'counterpart_um_id' => $otherMember->um_id,
            'invited_by_um_id' => $owner->um_id, 'meeting_at' => now()->subYear(), 'mode' => 'online',
            'details' => 'An older completed meeting.', 'created_at' => now()->subYear(), 'updated_at' => now()->subYear(),
        ]);

        $this->actingAs($owner, 'member')->get(route('dashboard.index', ['period' => 'last_30']))
            ->assertOk()
            ->assertSee('aria-label="1 meetings done"', false)
            ->assertSee('aria-label="View my meetings"', false)
            ->assertDontSee('analytics-stat stat-meeting', false);
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
            ->assertSee('Activity')
            ->assertSee('Last 30 days')
            ->assertSee('All time')
            ->assertSee('Reviews')
            ->assertSee('"recent":{"meetings":1,"references":1,"activity":1,"reviews":1}', false);
    }
}
