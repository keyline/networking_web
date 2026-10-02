<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEmailOtpAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_grant_a_registered_user_admin_access(): void
    {
        $superAdmin = $this->admin('ma');
        $member = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'admin-candidate-'.uniqid(),
            'um_mobile_no' => '9'.random_int(100000000, 999999999),
            'um_email_id' => uniqid('candidate-').'@example.test',
            'um_status' => 2,
            'um_profile_type' => 'O',
        ]);
        $this->attachBusiness($member, 'Admin Candidate Business');

        $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.access.toggle', $member), ['enabled' => 1])
            ->assertRedirect();

        $assigned = Admin::where('user_master_id', $member->um_id)->firstOrFail();
        $this->assertSame(1, $assigned->status);
    }

    public function test_guest_is_hidden_and_cannot_receive_admin_access(): void
    {
        $superAdmin = $this->admin('ma');
        $guest = UserMaster::create([
            'um_utm_id' => 1,
            'um_user_name' => 'guest-admin-candidate-'.uniqid(),
            'um_mobile_no' => '9'.random_int(100000000, 999999999),
            'um_email_id' => uniqid('guest-candidate-').'@example.test',
            'um_status' => 2,
            'um_profile_type' => 'G',
        ]);

        $this->withSession([
            'user_id' => $superAdmin->id,
            'name' => $superAdmin->name,
            'type' => $superAdmin->type,
            'email' => $superAdmin->email,
            'company_id' => $superAdmin->company_id,
            'is_admin_login' => 1,
        ])->actingAs($superAdmin, 'admin')
            ->get(route('admin.access.index'))
            ->assertOk()
            ->assertDontSee($guest->um_email_id);

        $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.access.toggle', $guest), ['enabled' => 1])
            ->assertStatus(422);

        $this->assertDatabaseMissing('admins', ['user_master_id' => $guest->um_id]);
    }

    public function test_legacy_guest_admin_record_cannot_sign_in(): void
    {
        $guest = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'legacy-guest-admin-'.uniqid(),
            'um_mobile_no' => '9'.random_int(100000000, 999999999),
            'um_email_id' => uniqid('legacy-guest-').'@example.test',
            'um_status' => 2, 'um_profile_type' => 'G',
        ]);
        $admin = $this->admin('s');
        $admin->update(['user_master_id' => $guest->um_id]);

        $this->post('/admin', [
            'email' => $admin->email,
            'password' => 'unused-password',
        ])->assertSessionHas('error_message');

        $this->assertGuest('admin');
    }

    public function test_admin_can_login_with_email_and_password(): void
    {
        $admin = $this->admin('s');

        $this->post('/admin', [
            'email' => $admin->email,
            'password' => 'unused-password',
        ])->assertRedirect('admin/dashboard');

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertSame('credentials', session('admin_login_source'));
        ob_start();
        $dashboardResponse = $this->get('/admin/dashboard');
        $dashboardHtml = (string) ob_get_clean();
        $dashboardResponse->assertOk();
        $this->assertStringContainsString('<span class="nav-link-title">Bulk Import</span>', $dashboardHtml);
        $this->assertStringContainsString('<span class="nav-link-title">Settings</span>', $dashboardHtml);
    }

    public function test_admin_login_page_only_offers_email_and_password(): void
    {
        $view = file_get_contents(resource_path('views/admin/maincontents/signin.blade.php'));

        $this->assertStringContainsString('Email address', $view);
        $this->assertStringContainsString('Password', $view);
        $this->assertStringNotContainsString('Email OTP', $view);
        $this->assertStringNotContainsString('Mobile OTP', $view);
        $this->assertStringNotContainsString('User ID', $view);
    }

    public function test_delegated_admin_cannot_manage_admin_access(): void
    {
        $delegated = $this->admin('s');
        $this->actingAs($delegated, 'admin')->get(route('admin.access.index'))->assertForbidden();
    }

    public function test_admin_access_uses_the_shared_full_width_admin_workspace(): void
    {
        $layout = file_get_contents(resource_path('views/admin/layout-after-login.blade.php'));
        $page = file_get_contents(resource_path('views/admin/maincontents/admin-access.blade.php'));

        $this->assertStringContainsString('content container-fluid admin-page-content', $layout);
        $this->assertStringContainsString('#content.main > .admin-page-content', $layout);
        $this->assertStringNotContainsString('<main id="content"', $page);
        $this->assertStringNotContainsString('content container-fluid', $page);
    }

    public function test_registered_member_with_admin_access_can_open_the_admin_dashboard(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'admin-dashboard-member-'.uniqid(),
            'um_mobile_no' => '9'.random_int(100000000, 999999999),
            'um_email_id' => uniqid('admin-dashboard-member-').'@example.test',
            'um_status' => 2,
            'um_profile_type' => 'O',
        ]);
        $this->attachBusiness($member, 'Admin Dashboard Business');
        $admin = $this->admin('s');
        $admin->update(['user_master_id' => $member->um_id]);

        $this->actingAs($member, 'member')
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Admin dashboard')
            ->assertSee('target="_blank"', false)
            ->assertSee(route('member.admin-dashboard'), false);

        $this->actingAs($member, 'member')
            ->post(route('member.admin-dashboard'))
            ->assertRedirect('/admin/dashboard')
            ->assertSessionHas('is_admin_login', 1)
            ->assertSessionHas('admin_login_source', 'member_dashboard');

        $this->assertAuthenticatedAs($admin->fresh(), 'admin');
        ob_start();
        $delegatedDashboard = $this->get('/admin/dashboard');
        $delegatedDashboardHtml = (string) ob_get_clean();
        $delegatedDashboard->assertOk();
        foreach (['Bulk Import', 'Memberships', 'Chapters', 'Analytics', 'Business Categories', 'Email Logs', 'Login Logs', 'Settings'] as $hiddenNavigation) {
            $this->assertStringNotContainsString('<span class="nav-link-title">'.$hiddenNavigation.'</span>', $delegatedDashboardHtml);
        }
        $this->assertStringContainsString('<span class="nav-link-title">Membership Accounts</span>', $delegatedDashboardHtml);
        $this->assertDatabaseHas('user_activities', [
            'user_email' => $admin->email,
            'user_type' => 'ADMIN',
            'activity_type' => 1,
            'activity_details' => 'Login Success via Member Dashboard',
            'platform_type' => 'WEB',
        ]);
    }

    public function test_member_without_admin_access_does_not_see_the_admin_dashboard_button(): void
    {
        $member = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'ordinary-member-'.uniqid(),
            'um_mobile_no' => '9'.random_int(100000000, 999999999),
            'um_email_id' => uniqid('ordinary-member-').'@example.test',
            'um_status' => 2,
            'um_profile_type' => 'O',
        ]);
        $this->attachBusiness($member, 'Ordinary Member Business');

        $this->actingAs($member, 'member')
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertDontSee('Admin dashboard');

        $this->actingAs($member, 'member')
            ->post(route('member.admin-dashboard'))
            ->assertNotFound();
    }

    private function admin(string $type): Admin
    {
        return Admin::create([
            'company_id' => 0,
            'name' => $type === 'ma' ? 'Test Super Admin' : 'Test Delegated Admin',
            'type' => $type,
            'email' => uniqid($type.'-').'@example.test',
            'login_id' => uniqid('login-'),
            'mobile' => '8'.random_int(100000000, 999999999),
            'password' => Hash::make('unused-password'),
            'status' => 1,
        ]);
    }

    private function attachBusiness(UserMaster $member, string $name): void
    {
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => $name,
            'cmpd_description' => 'Business linked to an eligible administrative member.',
            'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert([
            'ucm_um_id' => $member->um_id,
            'ucm_cmp_id' => $company->cmp_id,
        ]);
    }
}
