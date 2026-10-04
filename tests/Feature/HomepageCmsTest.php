<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Events\Event;
use App\Models\HomepageSection;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HomepageCmsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_create_a_section_with_ordered_items(): void
    {
        $admin = Admin::create([
            'company_id' => 0,
            'name' => 'Homepage Admin',
            'type' => 'ma',
            'email' => uniqid('homepage-').'@example.test',
            'login_id' => uniqid('home-'),
            'mobile' => '8'.random_int(100000000, 999999999),
            'password' => Hash::make('password'),
            'status' => 1,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.homepage.store'), [
            'type' => 'testimonials',
            'name' => 'Member testimonials',
            'eyebrow' => 'Member stories',
            'title' => 'What members say',
            'body' => 'Real experiences from our community.',
            'sort_order' => 40,
            'is_active' => 1,
            'items' => [
                ['title' => 'Asha Sen', 'body' => 'The network helped our company find trusted partners.', 'meta_role' => 'Founder, Example Co.'],
                ['title' => 'Raj Das', 'body' => 'We received valuable referrals.', 'meta_role' => 'Consultant'],
            ],
        ]);

        $response->assertRedirect(route('admin.homepage.index'));
        $section = HomepageSection::where('name', 'Member testimonials')->firstOrFail();
        $this->assertTrue($section->is_active);
        $this->assertSame(2, $section->items()->count());
        $this->assertSame('Asha Sen', $section->items()->first()->title);
    }

    public function test_public_homepage_renders_active_sections_and_hides_inactive_sections(): void
    {
        $active = HomepageSection::create([
            'type' => 'testimonials', 'name' => 'Visible stories', 'title' => 'Trusted by local businesses',
            'sort_order' => 41, 'is_active' => true,
        ]);
        $active->items()->create([
            'title' => 'Test Member', 'body' => 'A clean and useful community experience.',
            'meta' => ['role' => 'Business owner'], 'sort_order' => 0, 'is_active' => true,
        ]);
        HomepageSection::create([
            'type' => 'content', 'name' => 'Hidden block', 'title' => 'This must stay hidden',
            'sort_order' => 42, 'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Trusted by local businesses')
            ->assertSee('A clean and useful community experience.')
            ->assertDontSee('Register as guest')
            ->assertDontSee('This must stay hidden');
    }

    public function test_homepage_shows_only_events_currently_open_for_registration(): void
    {
        $open = Event::create([
            'title' => 'Open Networking Breakfast',
            'slug' => 'open-networking-breakfast',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDays(5),
            'status' => 'published',
            'currency' => 'INR',
        ]);
        $open->tickets()->create([
            'name' => 'General admission',
            'price' => 0,
            'maximum_per_order' => 5,
            'is_active' => true,
        ]);

        $notOpen = Event::create([
            'title' => 'Registration Opens Later',
            'slug' => 'registration-opens-later',
            'starts_at' => now()->addWeeks(2),
            'ends_at' => now()->addWeeks(2)->addHours(2),
            'registration_opens_at' => now()->addDays(3),
            'status' => 'published',
            'currency' => 'INR',
        ]);
        $notOpen->tickets()->create([
            'name' => 'General admission',
            'price' => 0,
            'maximum_per_order' => 5,
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Open for registration')
            ->assertSee('Open Networking Breakfast')
            ->assertSee('Register')
            ->assertSee(route('events.show', $open), false)
            ->assertDontSee('Registration Opens Later');
    }

    public function test_homepage_slider_shows_only_active_sponsored_businesses(): void
    {
        $sponsoredCompany = CompaniesMaster::create([]);
        $sponsored = CompaniesDetail::create([
            'cmpd_cmp_id' => $sponsoredCompany->cmp_id,
            'cmpd_name' => 'Featured Network Business',
            'cmpd_description' => 'A sponsored business shown in the homepage carousel.',
            'cmpd_status' => 1,
            'cmpd_is_sponsored' => 1,
        ]);
        $ordinaryCompany = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $ordinaryCompany->cmp_id,
            'cmpd_name' => 'Ordinary Network Business',
            'cmpd_description' => 'A regular business that is not sponsored.',
            'cmpd_status' => 1,
            'cmpd_is_sponsored' => 0,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Sponsored members')
            ->assertSee('Featured Network Business')
            ->assertSee(route('business.show', $sponsored->public_slug), false)
            ->assertSee('id="sponsoredTrack"', false)
            ->assertDontSee('Ordinary Network Business');
    }

    public function test_authenticated_member_header_replaces_join_actions_with_account_controls(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'EN009991',
            'um_email_id' => 'header-member@example.test',
            'um_mobile_no' => '9876509991',
            'um_status' => 2,
        ]);
        UserDetails::create([
            'ud_um_id' => $member->um_id,
            'ud_first_name' => 'Header',
            'ud_last_name' => 'Member',
        ]);

        $this->actingAs($member, 'member')->get('/')
            ->assertOk()
            ->assertSee('Header Member')
            ->assertSee('Dashboard')
            ->assertSee('Log out')
            ->assertDontSee('Join as member')
            ->assertDontSee('Register as guest')
            ->assertDontSee('Member login');
    }

    public function test_homepage_member_total_counts_only_active_approved_members(): void
    {
        $initialActiveMembers = UserMaster::activeMembers()->count();

        UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'Active Counted Member',
            'um_email_id' => uniqid('active-counted-').'@example.test',
            'um_mobile_no' => '9'.random_int(100000000, 999999999),
            'um_status' => 2,
        ]);
        UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'Pending Uncounted Member',
            'um_email_id' => uniqid('pending-uncounted-').'@example.test',
            'um_mobile_no' => '8'.random_int(100000000, 999999999),
            'um_status' => 1,
        ]);
        UserMaster::create([
            'um_utm_id' => 1,
            'um_user_name' => 'Active Uncounted Guest',
            'um_email_id' => uniqid('guest-uncounted-').'@example.test',
            'um_mobile_no' => '7'.random_int(100000000, 999999999),
            'um_status' => 2,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('homeStats', fn (array $stats) => $stats['members'] === $initialActiveMembers + 1);
    }
}
