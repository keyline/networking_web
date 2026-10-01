<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
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
        UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'Owner Test',
            'um_email_id' => 'owner-list@example.test',
            'um_mobile_no' => '9876500011',
            'um_status' => 2,
        ]);
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
        UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'Filtered Owner',
            'um_email_id' => 'filtered-owner@example.test',
            'um_mobile_no' => '9876500021',
            'um_status' => 2,
        ]);
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
}
