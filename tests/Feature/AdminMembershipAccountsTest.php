<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminMembershipAccountsTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_membership_register_only_lists_sellers_with_a_business(): void
    {
        $guest = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'Register Guest Filter',
            'um_email_id' => 'membership-guest-filter@example.test',
            'um_mobile_no' => '9876500881', 'um_status' => 2,
        ]);
        $seller = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => 'Register Seller Filter',
            'um_email_id' => 'membership-seller-filter@example.test',
            'um_mobile_no' => '9876500882', 'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => 'Membership Seller Business',
            'cmpd_description' => 'Business used to verify the seller-only membership register.',
            'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert([
            'ucm_um_id' => $seller->um_id,
            'ucm_cmp_id' => $company->cmp_id,
        ]);

        $this->asAdmin()
            ->get(route('admin.memberships.index'))
            ->assertOk()
            ->assertSee('membership-seller-filter@example.test')
            ->assertDontSee('membership-guest-filter@example.test')
            ->assertSee('Member')
            ->assertSee('Free')
            ->assertDontSee('Pending setup');

        $this->asAdmin()
            ->get(route('admin.memberships.index', ['status' => 'paid']))
            ->assertOk()
            ->assertDontSee('membership-seller-filter@example.test');
    }
}
