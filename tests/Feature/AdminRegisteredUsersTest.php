<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\PublicRegistrationSetting;
use App\Models\Country;
use App\Models\State;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminRegisteredUsersTest extends TestCase
{
    use DatabaseTransactions;

    private function signInAsAdmin(): self
    {
        $admin = Admin::query()->firstOrFail();

        return $this->withSession([
            'user_id' => $admin->id,
            'name' => $admin->name,
            'type' => $admin->type,
            'email' => $admin->email,
            'company_id' => $admin->company_id,
            'is_admin_login' => 1,
        ])->actingAs($admin, 'admin');
    }

    public function test_registered_users_page_lists_all_supported_user_types(): void
    {
        $owner = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'Owner Test',
            'um_email_id' => 'owner-list@example.test',
            'um_mobile_no' => '9876500011',
            'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => 'Filtered Owner Business',
            'cmpd_description' => 'Business used to verify user classification.',
            'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_um_id' => $owner->um_id, 'ucm_cmp_id' => $company->cmp_id]);
        UserMaster::create([
            'um_utm_id' => 3,
            'um_user_name' => 'Visitor Test',
            'um_email_id' => 'visitor-list@example.test',
            'um_mobile_no' => '9876500012',
            'um_status' => 2,
        ]);

        $this->signInAsAdmin()
            ->get('/admin/clients/registered-users')
            ->assertOk()
            ->assertSee('Registered Users')
            ->assertSee('owner-list@example.test')
            ->assertSee('visitor-list@example.test');
    }

    public function test_registered_users_page_filters_business_owners_and_visitors(): void
    {
        $owner = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'Filtered Owner',
            'um_email_id' => 'filtered-owner@example.test',
            'um_mobile_no' => '9876500021',
            'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => 'Filtered Owner Business',
            'cmpd_description' => 'Business used to verify user classification.',
            'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_um_id' => $owner->um_id, 'ucm_cmp_id' => $company->cmp_id]);
        UserMaster::create([
            'um_utm_id' => 3,
            'um_user_name' => 'Filtered Visitor',
            'um_email_id' => 'filtered-visitor@example.test',
            'um_mobile_no' => '9876500022',
            'um_status' => 2,
        ]);

        $this->signInAsAdmin()
            ->get('/admin/clients/registered-users?type=owner')
            ->assertOk()
            ->assertSee('filtered-owner@example.test')
            ->assertDontSee('filtered-visitor@example.test');

        $this->get('/admin/clients/registered-users?type=visitor')
            ->assertOk()
            ->assertSee('filtered-visitor@example.test')
            ->assertDontSee('filtered-owner@example.test');
    }

    public function test_pending_business_owner_has_separate_approval_buttons(): void
    {
        $owner = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'Pending Owner',
            'um_email_id' => 'pending-owner@example.test',
            'um_mobile_no' => '9876500023',
            'um_status' => 1,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => 'Pending Owner Business',
            'cmpd_description' => 'A business awaiting combined approval.',
            'cmpd_status' => 0,
        ]);
        DB::table('user_companies_map')->insert(['ucm_um_id' => $owner->um_id, 'ucm_cmp_id' => $company->cmp_id]);

        $this->signInAsAdmin()
            ->get(route('admin.clients.registered-members'))
            ->assertOk()
            ->assertSee('pending-owner@example.test')
            ->assertSee('Approve member')
            ->assertSee('Approve business')
            ->assertSee('disabled', false)
            ->assertSee(route('admin.registrations.approve-member', $owner), false);
    }

    public function test_member_must_be_approved_before_business_approval(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'Approval Test',
            'um_email_id' => 'combined-approval@example.test', 'um_mobile_no' => '9876500093', 'um_status' => 1,
        ]);
        $company = CompaniesMaster::create([]);
        $details = CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Combined Approval Business',
            'cmpd_description' => 'Pending business for an active member.', 'cmpd_status' => 0,
        ]);
        DB::table('user_companies_map')->insert(['ucm_um_id' => $member->um_id, 'ucm_cmp_id' => $company->cmp_id]);

        $this->signInAsAdmin()
            ->post(route('admin.registrations.approve-business', [$member, $company]))
            ->assertStatus(422);
        $this->assertSame(1, (int) $member->fresh()->um_status);
        $this->assertSame(0, (int) $details->fresh()->cmpd_status);

        $this->post(route('admin.registrations.approve-member', $member))
            ->assertRedirect()
            ->assertSessionHas('success_message');
        $this->assertSame(2, (int) $member->fresh()->um_status);
        $this->assertSame(0, (int) $details->fresh()->cmpd_status);

        $this->post(route('admin.registrations.approve-business', [$member, $company]))
            ->assertRedirect()
            ->assertSessionHas('success_message');
        $this->assertSame(1, (int) $details->fresh()->cmpd_status);
    }

    public function test_dashboard_lists_pending_requests_and_locks_business_approval(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'Dashboard Pending Member',
            'um_email_id' => 'dashboard-pending@example.test', 'um_mobile_no' => '9876500193', 'um_status' => 1,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Dashboard Pending Business',
            'cmpd_description' => 'Waiting for two-step approval.', 'cmpd_status' => 0,
        ]);
        DB::table('user_companies_map')->insert(['ucm_um_id' => $member->um_id, 'ucm_cmp_id' => $company->cmp_id]);

        $this->signInAsAdmin();
        ob_start();
        $response = $this->get('/admin/dashboard');
        $html = (string) ob_get_clean();

        $response->assertOk();
        $this->assertStringContainsString('Pending member requests', $html);
        $this->assertStringContainsString('dashboard-pending@example.test', $html);
        $this->assertStringContainsString('Dashboard Pending Business', $html);
        $this->assertStringContainsString('Profile', $html);
        $this->assertStringContainsString('Business', $html);
        $this->assertStringContainsString('data-bs-target="#pending-profile-'.$member->um_id.'"', $html);
        $this->assertStringContainsString('data-bs-target="#pending-business-'.$member->um_id.'-'.$company->cmp_id.'"', $html);
        $this->assertStringContainsString('View member profile', $html);
        $this->assertStringContainsString('Profile details', $html);
        $this->assertStringContainsString('Business details', $html);
        $this->assertStringContainsString(route('admin.registrations.approve-member', $member), $html);
        $this->assertStringContainsString('Approve profile', $html);
        $this->assertStringContainsString('Approve profile first', $html);
        $this->assertStringContainsString('disabled', $html);
    }

    public function test_dashboard_member_total_counts_only_active_approved_members(): void
    {
        $initialActiveMembers = UserMaster::activeMembers()->count();
        UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'Dashboard Active Count',
            'um_email_id' => uniqid('dashboard-active-').'@example.test',
            'um_mobile_no' => '6'.random_int(100000000, 999999999), 'um_status' => 2,
        ]);
        UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'Dashboard Pending Count',
            'um_email_id' => uniqid('dashboard-pending-count-').'@example.test',
            'um_mobile_no' => '5'.random_int(100000000, 999999999), 'um_status' => 1,
        ]);

        $this->signInAsAdmin();
        ob_start();
        $response = $this->get('/admin/dashboard');
        $html = (string) ob_get_clean();

        $response->assertOk();
        $this->assertStringContainsString(
            'data-stat="active-members">'.($initialActiveMembers + 1).'</h2>',
            $html
        );
    }

    public function test_business_sponsored_button_persists_without_livewire(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'Sponsor Owner',
            'um_email_id' => 'sponsor-owner@example.test', 'um_mobile_no' => '9876500094', 'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        $details = CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Sponsor Toggle Business',
            'cmpd_description' => 'Business used for sponsored persistence.',
            'cmpd_status' => 1, 'cmpd_is_sponsored' => 0,
        ]);
        DB::table('user_companies_map')->insert(['ucm_um_id' => $member->um_id, 'ucm_cmp_id' => $company->cmp_id]);

        $this->signInAsAdmin()
            ->post(route('admin.clients.business.sponsored', $company->cmp_id), ['sponsored' => 1])
            ->assertRedirect()
            ->assertSessionHas('success_message');

        $this->assertSame(1, (int) $details->fresh()->cmpd_is_sponsored);
    }

    public function test_users_with_businesses_and_guest_users_have_separate_pages(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'Business Member',
            'um_email_id' => 'business-member@example.test', 'um_mobile_no' => '9876500024', 'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Member Business',
            'cmpd_description' => 'A member-owned business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_um_id' => $member->um_id, 'ucm_cmp_id' => $company->cmp_id]);
        UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'Unpaid Guest',
            'um_email_id' => 'unpaid-guest@example.test', 'um_mobile_no' => '9876500025', 'um_status' => 2,
        ]);

        $this->signInAsAdmin()
            ->get(route('admin.clients.registered-members'))
            ->assertOk()->assertSee('Registered Members')->assertSee('business-member@example.test')->assertSee('Member Business')
            ->assertDontSee('unpaid-guest@example.test');

        $this->get(route('admin.clients.guest-users'))
            ->assertOk()->assertSee('Guest Users')->assertSee('Registered Guest')->assertSee('unpaid-guest@example.test')
            ->assertDontSee('business-member@example.test');
    }

    public function test_join_registration_switch_is_managed_from_the_dashboard(): void
    {
        PublicRegistrationSetting::current()->update(['enabled' => false]);

        $dashboard = file_get_contents(resource_path('views/admin/maincontents/dashboard.blade.php'));
        $registeredUsers = file_get_contents(resource_path('views/admin/maincontents/client/registered-users.blade.php'));

        $this->assertStringContainsString('Public Join registration', $dashboard);
        $this->assertStringContainsString("route('admin.dashboard.registration-setting')", $dashboard);
        $this->assertStringNotContainsString('Public Join registration', $registeredUsers);

        $this->signInAsAdmin()->post(route('admin.dashboard.registration-setting'), [
            'public_registration_enabled' => 1,
            'public_registration_closed_message' => 'Registration is temporarily closed.',
        ])->assertRedirect();

        $this->assertTrue((bool) PublicRegistrationSetting::current()->fresh()->enabled);
    }

    public function test_super_admin_can_create_an_active_business_member(): void
    {
        $admin = Admin::query()->firstOrFail();
        $admin->update(['type' => 'ma']);
        $state = State::query()->firstOrFail();
        $country = Country::findOrFail($state->country_id);
        $category = BusinessCategoryMaster::where('status', 1)->firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.clients.registered-members.create'), [
            'first_name' => 'Admin Added', 'email' => 'admin-added-member@example.test',
            'mobile' => '9876507733', 'business_name' => 'Admin Added Business',
            'category_ids' => [$category->bcm_id], 'country' => $country->id,
            'state' => $state->id, 'pincode' => '700001',
        ])->assertRedirect(route('admin.clients.registered-members'));

        $member = UserMaster::where('um_email_id', 'admin-added-member@example.test')->firstOrFail();
        $this->assertSame(2, (int) $member->um_utm_id);
        $this->assertSame(2, (int) $member->um_status);
        $this->assertSame(1, (int) $member->companies()->with('details')->firstOrFail()->details->cmpd_status);
    }

    public function test_admin_can_add_another_business_by_name_for_an_existing_member(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'Multi Business Member',
            'um_email_id' => 'multi-business-admin@example.test',
            'um_mobile_no' => '9876507744', 'um_status' => 2,
        ]);
        $firstCompany = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $firstCompany->cmp_id, 'cmpd_name' => 'Existing Business',
            'cmpd_description' => 'The member existing business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_um_id' => $member->um_id, 'ucm_cmp_id' => $firstCompany->cmp_id]);

        $this->signInAsAdmin()
            ->post(route('admin.clients.registered-members.businesses.store', $member), [
                'business_name' => 'Admin Added Second Business',
            ])
            ->assertRedirect()
            ->assertSessionHas('success_message');

        $member->refresh();
        $this->assertCount(2, $member->companies);
        $this->assertDatabaseHas('companies_details', [
            'cmpd_name' => 'Admin Added Second Business',
            'cmpd_status' => 1,
        ]);

        $this->actingAs($member, 'member')->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Admin Added Second Business')
            ->assertDontSee('Add business');
    }

    public function test_admin_cannot_add_a_business_to_a_guest(): void
    {
        $guest = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'Businessless Guest',
            'um_email_id' => 'businessless-guest@example.test',
            'um_mobile_no' => '9876507755', 'um_status' => 2,
        ]);

        $this->signInAsAdmin()
            ->post(route('admin.clients.registered-members.businesses.store', $guest), [
                'business_name' => 'Forbidden Guest Business',
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('companies_details', ['cmpd_name' => 'Forbidden Guest Business']);
    }

    public function test_admin_can_permanently_delete_a_user_business_and_uploaded_files(): void
    {
        $user = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'Delete Test',
            'um_email_id' => 'delete-user@example.test',
            'um_mobile_no' => '9876500031',
            'um_status' => 1,
        ]);
        DB::table('user_details')->insert([
            'ud_um_id' => $user->um_id,
            'ud_first_name' => 'Delete',
            'ud_last_name' => 'Test',
            'ud_profile_image' => 'delete-user-test.jpg',
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => 'Delete Test Business',
            'cmpd_description' => 'Temporary deletion test business.',
            'cmpd_logo' => 'delete-company-test.jpg',
            'cmpd_status' => 0,
        ]);
        DB::table('user_companies_map')->insert(['ucm_um_id' => $user->um_id, 'ucm_cmp_id' => $company->cmp_id]);
        DB::table('business_portfolios')->insert([
            'company_id' => $company->cmp_id,
            'tagline' => 'Delete me',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        File::ensureDirectoryExists(public_path('uploads/user'));
        File::ensureDirectoryExists(public_path('uploads/company'));
        File::ensureDirectoryExists(public_path('uploads/portfolio/'.$company->cmp_id.'/gallery'));
        File::put(public_path('uploads/user/delete-user-test.jpg'), 'test');
        File::put(public_path('uploads/company/delete-company-test.jpg'), 'test');
        File::put(public_path('uploads/portfolio/'.$company->cmp_id.'/gallery/test.jpg'), 'test');

        $this->signInAsAdmin()
            ->delete(route('admin.clients.registered-users.destroy', $user))
            ->assertRedirect(route('admin.clients.registered-users'))
            ->assertSessionHas('success_message');

        $this->assertDatabaseMissing('user_master', ['um_id' => $user->um_id]);
        $this->assertDatabaseMissing('companies_master', ['cmp_id' => $company->cmp_id]);
        $this->assertDatabaseMissing('business_portfolios', ['company_id' => $company->cmp_id]);
        $this->assertFileDoesNotExist(public_path('uploads/user/delete-user-test.jpg'));
        $this->assertFileDoesNotExist(public_path('uploads/company/delete-company-test.jpg'));
        $this->assertDirectoryDoesNotExist(public_path('uploads/portfolio/'.$company->cmp_id));
    }

    public function test_bulk_cleanup_requires_exact_confirmation_and_starts_without_deleting_immediately(): void
    {
        $user = UserMaster::create([
            'um_utm_id' => 3,
            'um_user_name' => 'Bulk Start Test',
            'um_email_id' => 'bulk-start@example.test',
            'um_mobile_no' => '9876500041',
            'um_status' => 2,
        ]);

        $this->signInAsAdmin()
            ->postJson(route('admin.clients.registered-users.purge.start'), ['confirmation' => 'delete all'])
            ->assertUnprocessable();

        $this->postJson(route('admin.clients.registered-users.purge.start'), ['confirmation' => 'DELETE ALL'])
            ->assertOk()
            ->assertJsonStructure(['token', 'total_users', 'total_businesses', 'message']);

        $this->assertDatabaseHas('user_master', ['um_id' => $user->um_id]);
    }
}
