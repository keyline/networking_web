<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEmailOtpAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_grant_a_registered_user_admin_access(): void
    {
        $superAdmin = $this->admin('ma');
        $member = UserMaster::create([
            'um_utm_id' => 3,
            'um_user_name' => 'admin-candidate-'.uniqid(),
            'um_mobile_no' => '9'.random_int(100000000, 999999999),
            'um_email_id' => uniqid('candidate-').'@example.test',
            'um_status' => 2,
            'um_profile_type' => 'O',
        ]);

        $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.access.toggle', $member), ['enabled' => 1])
            ->assertRedirect();

        $assigned = Admin::where('user_master_id', $member->um_id)->firstOrFail();
        $this->assertSame(1, $assigned->status);
    }

    public function test_admin_can_login_with_email_and_password(): void
    {
        $admin = $this->admin('s');

        $this->post('/admin', [
            'email' => $admin->email,
            'password' => 'unused-password',
        ])->assertRedirect('admin/dashboard');

        $this->assertAuthenticatedAs($admin, 'admin');
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
}
