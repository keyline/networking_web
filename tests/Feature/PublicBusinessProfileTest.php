<?php

namespace Tests\Feature;

use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\BusinessPortfolio;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BusinessLeadEmailNotification;
use App\Notifications\BusinessLeadSmsNotification;
use Tests\TestCase;

class PublicBusinessProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function test_business_name_generates_a_unique_public_slug(): void
    {
        $firstCompany = CompaniesMaster::create([]);
        $secondCompany = CompaniesMaster::create([]);

        $first = CompaniesDetail::create([
            'cmpd_cmp_id' => $firstCompany->cmp_id,
            'cmpd_name' => 'Keyline DigiTech Pvt Ltd',
            'cmpd_description' => 'First business.',
            'cmpd_status' => 1,
            'cmpd_is_document_valid' => '1',
        ]);
        $second = CompaniesDetail::create([
            'cmpd_cmp_id' => $secondCompany->cmp_id,
            'cmpd_name' => 'Keyline DigiTech Pvt Ltd',
            'cmpd_description' => 'Second business.',
            'cmpd_status' => 1,
            'cmpd_is_document_valid' => '1',
        ]);

        $this->assertSame('keyline-digitech-pvt-ltd', $first->public_slug);
        $this->assertSame('keyline-digitech-pvt-ltd-2', $second->public_slug);

        $second->update(['cmpd_name' => 'Another Business']);
        $this->assertSame('another-business', $second->fresh()->public_slug);
    }

    public function test_anyone_can_view_active_business_and_send_a_lead(): void
    {
        Notification::fake();
        $owner = UserMaster::create(['um_utm_id' => 2, 'um_user_name' => 'EN000001', 'um_email_id' => 'owner@example.test', 'um_mobile_no' => '9876543210', 'um_status' => 2]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create(['cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Example Business', 'public_slug' => 'example-business', 'cmpd_description' => 'A useful company.', 'cmpd_status' => 1, 'cmpd_is_document_valid' => '1']);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);
        BusinessPortfolio::create([
            'company_id' => $company->cmp_id, 'is_published' => true,
            'published_snapshot' => [
                'whatsapp_enabled' => true, 'whatsapp_number' => '919876543210', 'contact_form_enabled' => true,
                'items' => [['title' => 'Tax consulting', 'description' => 'GST and taxation support', 'price_label' => 'Ask for price', 'image' => null]],
                'media' => [],
            ],
        ]);

        DB::table('reviews')->insert([
            'rev_cmp_id' => $company->cmp_id,
            'rev_um_id' => $owner->um_id,
            'rev_rating' => 4.5,
            'rev_comment' => 'Professional service and a quick response.',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('business.show', 'example-business'))->assertOk()->assertSee('Example Business')->assertSee('Send enquiry')->assertSee('Tax consulting')->assertSee('Customer reviews')->assertSee('4.5')->assertSee('Professional service and a quick response.')->assertSee('wa.me/919876543210', false);

        $response = $this->post(route('business.lead', 'example-business'), [
            'name' => 'Prospective Customer', 'email' => 'lead@example.test', 'phone' => '9123456789',
            'subject' => 'Need a quotation', 'message' => 'Please contact me with your pricing.',
        ]);

        $response->assertRedirect(route('business.show', 'example-business'))->assertSessionHas('lead_success');
        $this->assertDatabaseHas('enquiry_master', ['enm_email' => 'lead@example.test', 'enm_subject' => 'Need a quotation']);
        $this->assertDatabaseHas('enquiry_to_user', ['etu_cmp_id' => $company->cmp_id, 'etu_um_id' => $owner->um_id]);
        Notification::assertSentTo($owner, BusinessLeadSmsNotification::class);
        Notification::assertSentOnDemand(BusinessLeadEmailNotification::class);

        $this->post(route('business.lead', 'example-business'), [
            'name' => 'Product Buyer', 'phone' => '9234567890', 'product_title' => 'Tax consulting',
            'message' => 'Please call me about this service.',
        ])->assertRedirect(route('business.show', 'example-business'));
        $this->assertDatabaseHas('enquiry_master', ['enm_name' => 'Product Buyer', 'enm_subject' => 'Enquiry about Tax consulting']);
    }
}
