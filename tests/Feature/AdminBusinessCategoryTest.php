<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\Companies\CompaniesMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminBusinessCategoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_create_merge_and_delete_categories_with_assignment_protection(): void
    {
        $admin = Admin::query()->firstOrFail();
        $this->withSession([
            'user_id' => $admin->id,
            'name' => $admin->name,
            'type' => $admin->type,
            'email' => $admin->email,
            'company_id' => $admin->company_id,
            'is_admin_login' => 1,
        ])->actingAs($admin, 'admin');

        $this->post(route('admin.business-categories.store'), ['name' => 'Test Advisory Source'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->post(route('admin.business-categories.store'), ['name' => 'Test Advisory Target'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $source = BusinessCategoryMaster::where('name', 'Test Advisory Source')->firstOrFail();
        $target = BusinessCategoryMaster::where('name', 'Test Advisory Target')->firstOrFail();
        $company = CompaniesMaster::create([]);
        DB::table('categories_to_companies')->insert([
            ['ctc_bcm_id' => $source->bcm_id, 'ctc_cmp_id' => $company->cmp_id, 'ctc_created_at' => now()],
            ['ctc_bcm_id' => $target->bcm_id, 'ctc_cmp_id' => $company->cmp_id, 'ctc_created_at' => now()],
        ]);

        $this->delete(route('admin.business-categories.destroy', $source))
            ->assertRedirect()
            ->assertSessionHasErrors('category');
        $this->assertDatabaseHas('business_category_master', ['bcm_id' => $source->bcm_id]);

        $this->post(route('admin.business-categories.merge'), [
            'source_category_id' => $source->bcm_id,
            'target_category_id' => $target->bcm_id,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('business_category_master', ['bcm_id' => $source->bcm_id]);
        $this->assertSame(1, DB::table('categories_to_companies')
            ->where('ctc_cmp_id', $company->cmp_id)
            ->where('ctc_bcm_id', $target->bcm_id)
            ->count());

        DB::table('categories_to_companies')->where('ctc_cmp_id', $company->cmp_id)->delete();
        $this->delete(route('admin.business-categories.destroy', $target))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('business_category_master', ['bcm_id' => $target->bcm_id]);
    }

    public function test_category_master_page_shows_assignment_counts(): void
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
            ->get(route('admin.business-categories.index'))
            ->assertOk()
            ->assertSee('Business category master')
            ->assertSee('Merge categories')
            ->assertSee('Assigned categories are protected from deletion.');
    }

    public function test_shared_pagination_uses_compact_bootstrap_controls_without_svg_arrows(): void
    {
        $admin = Admin::query()->firstOrFail();
        foreach (range(1, 26) as $index) {
            BusinessCategoryMaster::create([
                'parent_id' => 0,
                'name' => 'Pagination Test '.uniqid().'-'.$index,
                'slug' => 'pagination-test-'.uniqid().'-'.$index,
                'status' => 1,
            ]);
        }

        $this->withSession([
            'user_id' => $admin->id, 'name' => $admin->name, 'type' => $admin->type,
            'email' => $admin->email, 'company_id' => $admin->company_id, 'is_admin_login' => 1,
        ])->actingAs($admin, 'admin')
            ->get(route('admin.business-categories.index'))
            ->assertOk()
            ->assertSee('class="pagination"', false)
            ->assertSee('&rsaquo;', false)
            ->assertDontSee('<svg', false);
    }
}
