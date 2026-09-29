<?php

namespace Tests\Feature;

use App\Models\Admin;
use Tests\TestCase;

class AdminBusinessAnalyticsTest extends TestCase
{
    public function test_authenticated_admin_can_open_business_analytics_dashboard(): void
    {
        $admin = Admin::query()->firstOrFail();

        $this->withSession([
            'user_id' => $admin->id,
            'name' => $admin->name,
            'type' => $admin->type,
            'email' => $admin->email,
            'company_id' => $admin->company_id,
            'is_admin_login' => 1,
        ])->actingAs($admin, 'admin')
            ->get('/admin/analytics?range=30')
            ->assertOk()
            ->assertSee('Business Traction Analytics')
            ->assertSee('Business traction ranking')
            ->assertSee('WhatsApp clicks');
    }
}
