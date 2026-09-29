<?php

namespace Tests\Feature;

use App\Models\Admin;
use Tests\TestCase;

class AdminMembershipAccountsTest extends TestCase
{
    private function asAdmin()
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

    public function test_dashboard_opens_without_date_filters(): void
    {
        $this->asAdmin()
            ->get('/admin/membership-accounts')
            ->assertOk();
    }

    public function test_dashboard_accepts_an_explicit_date_range(): void
    {
        $this->asAdmin()
            ->get('/admin/membership-accounts?from=2026-09-01&to=2026-09-30')
            ->assertOk();
    }
}
