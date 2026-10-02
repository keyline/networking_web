<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminBusinessListTest extends TestCase
{
    use DatabaseTransactions;

    public function test_business_list_ignores_an_orphan_company_without_details(): void
    {
        $admin = Admin::query()->firstOrFail();
        $member = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'ORPHAN-TEST',
            'um_email_id' => 'orphan-business@example.test',
            'um_mobile_no' => '9876500001',
            'um_status' => 2,
        ]);
        $company = CompaniesMaster::create([]);
        DB::table('user_companies_map')->insert([
            'ucm_cmp_id' => $company->cmp_id,
            'ucm_um_id' => $member->um_id,
        ]);

        $listedCompany = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $listedCompany->cmp_id,
            'cmpd_name' => 'Admin Preview Business',
            'public_slug' => 'admin-preview-business',
            'cmpd_description' => 'Public preview description.',
            'cmpd_status' => 1,
            'cmpd_is_document_valid' => '1',
        ]);
        DB::table('user_companies_map')->insert([
            'ucm_cmp_id' => $listedCompany->cmp_id,
            'ucm_um_id' => $member->um_id,
        ]);

        $this->withSession([
            'user_id' => $admin->id,
            'name' => $admin->name,
            'type' => $admin->type,
            'email' => $admin->email,
            'company_id' => $admin->company_id,
            'is_admin_login' => 1,
        ])->actingAs($admin, 'admin')
            ->get('/admin/clients/business/list')
            ->assertOk()
            ->assertSee('Business List')
            ->assertDontSee('orphan-business@example.test')
            ->assertSee('Admin Preview Business')
            ->assertSee(route('business.show', ['slug' => 'admin-preview-business', 'preview' => 'visitor']), false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false);
    }
}
