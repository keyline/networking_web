<?php

namespace Tests\Feature;

use App\Models\Business\BusinessCategoryMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_registration_creates_pending_member_and_business(): void
    {
        $category = BusinessCategoryMaster::where('status', 1)->firstOrFail();

        $response = $this->post(route('join.store'), [
            'first_name' => 'Test',
            'last_name' => 'Owner',
            'email' => 'new-owner@example.test',
            'mobile' => '9876543210',
            'whatsapp' => '9876543210',
            'business_name' => 'Fresh Start Services',
            'business_category' => $category->bcm_id,
            'description' => 'Professional business services.',
            'address_line_1' => '1 Business Street',
            'city' => 'Kolkata',
            'pincode' => '700001',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('join.create'));
        $response->assertSessionHas('registration_success');
        $this->assertDatabaseHas('user_master', [
            'um_email_id' => 'new-owner@example.test',
            'um_mobile_no' => '9876543210',
            'um_status' => 1,
        ]);
        $this->assertDatabaseHas('companies_details', [
            'cmpd_name' => 'Fresh Start Services',
            'cmpd_status' => 0,
        ]);
        $this->assertDatabaseHas('categories_to_companies', [
            'ctc_bcm_id' => $category->bcm_id,
        ]);
    }
}
