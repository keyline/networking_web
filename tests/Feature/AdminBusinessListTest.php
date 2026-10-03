<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\BusinessPortfolio;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\Country;
use App\Models\State;
use App\Models\User\UserMaster;
use App\Helpers\Helper;
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

    public function test_admin_business_editor_matches_member_ui_and_updates_visibility(): void
    {
        $admin = Admin::query()->firstOrFail();
        $category = BusinessCategoryMaster::query()->where('status', 1)->firstOrFail();
        $country = Country::query()->where('status', 1)->firstOrFail();
        $state = State::query()->where('country_id', $country->id)->where('status', 1)->firstOrFail();
        $company = CompaniesMaster::create([]);
        $details = CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => 'Admin Editor Test Business',
            'cmpd_description' => 'A business used to test the admin editor.',
            'cmpd_phone' => '9876500021',
            'cmpd_country' => $country->id,
            'cmpd_state' => $state->id,
            'cmpd_status' => 1,
        ]);
        DB::table('categories_to_companies')->insert([
            'ctc_cmp_id' => $company->cmp_id,
            'ctc_bcm_id' => $category->bcm_id,
        ]);

        $session = [
            'user_id' => $admin->id,
            'name' => $admin->name,
            'type' => $admin->type,
            'email' => $admin->email,
            'company_id' => $admin->company_id,
            'is_admin_login' => 1,
        ];
        $url = '/admin/clients/business/info-edit/'.Helper::encoded($company->cmp_id);
        BusinessPortfolio::create([
            'company_id' => $company->cmp_id,
            'is_published' => true,
            'published_snapshot' => ['items' => []],
        ]);

        $template = file_get_contents(resource_path('views/admin/maincontents/client/business-add-edit.blade.php'));
        $this->assertStringContainsString('admin-business-editor', $template);
        $this->assertStringContainsString('Business profile', $template);
        $this->assertStringContainsString('Products / services', $template);
        $this->assertStringContainsString('Business visibility', $template);
        $this->assertStringContainsString('Search categories', $template);
        $this->assertStringContainsString('name="status"', $template);
        $this->assertStringContainsString('name="website"', $template);

        $this->withSession($session)->actingAs($admin, 'admin')->post($url, [
            'id' => $details->cmpd_id,
            'category_ids' => [$category->bcm_id],
            'regn_no' => '',
            'name' => 'Admin Editor Test Business',
            'description' => 'A business used to test the admin editor.',
            'email' => '',
            'alternate_email' => '',
            'phone' => '9876500021',
            'whatsapp_no' => '',
            'website' => 'https://admin-editor.example.test',
            'address1' => '',
            'address2' => '',
            'address3' => '',
            'estd_year' => '',
            'country' => $country->id,
            'state' => $state->id,
            'district' => '',
            'pincode' => '',
            'status' => 0,
        ])->assertRedirect($url);

        $this->withSession($session)->actingAs($admin, 'admin')->post(route('admin.clients.business.offerings.store', $company->cmp_id), [
            'type' => 'service',
            'title' => 'Admin managed service',
            'description' => 'Created from the admin business editor.',
            'price_label' => 'Ask for price',
        ])->assertRedirect($url.'?tab=offerings#offerings');

        $this->assertDatabaseHas('business_portfolio_items', [
            'company_id' => $company->cmp_id,
            'title' => 'Admin managed service',
            'type' => 'service',
        ]);
        $this->assertSame(
            'Admin managed service',
            BusinessPortfolio::where('company_id', $company->cmp_id)->firstOrFail()->published_snapshot['items'][0]['title']
        );

        $this->assertDatabaseHas('companies_details', [
            'cmpd_id' => $details->cmpd_id,
            'cmpd_status' => 0,
        ]);
        $this->assertDatabaseHas('business_portfolios', [
            'company_id' => $company->cmp_id,
            'website' => 'https://admin-editor.example.test',
        ]);
    }
}
