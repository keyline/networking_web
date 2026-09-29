<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User\UserMaster;
use App\Notifications\AdminLoginOtpNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminEmailOtpAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_grant_a_registered_user_admin_access_and_user_can_login_with_otp(): void
    {
        Notification::fake();
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

        auth('admin')->logout();
        $this->post('/admin', ['login_method' => 'email_otp', 'email' => $assigned->email])
            ->assertSessionHas('admin_otp_admin_id', $assigned->id)
            ->assertSessionHas('admin_otp_channel', 'email');

        $otp = null;
        Notification::assertSentTo($assigned, AdminLoginOtpNotification::class, function ($notification) use (&$otp) {
            $otp = $notification->otp;
            return true;
        });

        $this->withSession(['admin_otp_admin_id' => $assigned->id, 'admin_otp_channel' => 'email'])
            ->post(route('admin.login.verify-otp'), ['admin_id' => $assigned->id, 'otp' => $otp])
            ->assertRedirect('admin/dashboard');

        $this->assertAuthenticatedAs($assigned->fresh(), 'admin');
        $this->assertNull($assigned->fresh()->login_otp_hash);
    }

    public function test_admin_can_login_with_user_id_and_password(): void
    {
        $admin = $this->admin('s');

        $this->post('/admin', [
            'login_method' => 'password',
            'identifier' => $admin->login_id,
            'password' => 'unused-password',
        ])->assertRedirect('admin/dashboard');

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_can_request_and_verify_mobile_otp(): void
    {
        $admin = $this->admin('s');

        $response = $this->post('/admin', [
            'login_method' => 'mobile_otp',
            'mobile' => $admin->mobile,
        ])->assertSessionHas('admin_otp_channel', 'mobile');

        $otp = session('testing_admin_mobile_otp');
        $this->assertNotNull($otp);

        $this->withSession(['admin_otp_admin_id' => $admin->id, 'admin_otp_channel' => 'mobile'])
            ->post(route('admin.login.verify-otp'), ['admin_id' => $admin->id, 'otp' => $otp])
            ->assertRedirect('admin/dashboard');

        $this->assertAuthenticatedAs($admin->fresh(), 'admin');
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
