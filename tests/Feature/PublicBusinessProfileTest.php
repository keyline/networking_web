<?php

namespace Tests\Feature;

use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\BusinessPortfolio;
use App\Models\Business\BusinessCategoryMaster;
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
        CompaniesDetail::create(['cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Example Business', 'public_slug' => 'example-business', 'cmpd_description' => 'A useful company.', 'cmpd_email' => 'business@example.test', 'cmpd_phone' => '9876501234', 'cmpd_whatsapp_no' => '9876543210', 'cmpd_address1' => 'Private business address', 'cmpd_status' => 1, 'cmpd_is_document_valid' => '1']);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);
        BusinessPortfolio::create([
            'company_id' => $company->cmp_id, 'is_published' => true,
            'published_snapshot' => [
                'whatsapp_enabled' => true, 'whatsapp_number' => '919876543210', 'contact_form_enabled' => true,
                'items' => [['title' => 'Tax consulting', 'description' => 'GST and taxation support with filing, reconciliation, compliance reviews, and practical advice for growing businesses.', 'price_label' => 'Ask for price', 'image' => 'business-portfolios/example/offerings/tax-consulting.jpg']],
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

        $this->get(route('business.show', 'example-business'))->assertOk()->assertSee('Example Business')->assertSee('Send enquiry')->assertSee('Tax consulting')->assertSee('Customer reviews')->assertSee('4.5')->assertSee('Professional service and a quick response.')->assertSee('Sign in to view contact details')->assertDontSee('business@example.test')->assertDontSee('9876501234')->assertDontSee('Private business address')->assertDontSee('wa.me/919876543210', false);

        $visitor = UserMaster::create(['um_utm_id' => 3, 'um_user_name' => 'visitor', 'um_email_id' => 'visitor@example.test', 'um_mobile_no' => '9123409876', 'um_status' => 2]);
        $this->actingAs($visitor, 'member')->get(route('business.show', 'example-business'))
            ->assertOk()
            ->assertSee('business@example.test')
            ->assertSee('9876501234')
            ->assertSee('Private business address')
            ->assertSee('productPreviewDialog', false)
            ->assertSee('Preview Tax consulting')
            ->assertSee('Email enquiry')
            ->assertSee('I%20want%20to%20know%20more%20about%20this%3A%20Tax%20consulting', false)
            ->assertSee('business-portfolios%2Fexample%2Fofferings%2Ftax-consulting.jpg', false)
            ->assertSee('wa.me/919876543210', false);

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

    public function test_members_directory_lists_businesses_without_member_names(): void
    {
        $category = BusinessCategoryMaster::where('status', 1)->firstOrFail();
        $owner = UserMaster::create(['um_utm_id' => 2, 'um_user_name' => 'hidden-owner', 'um_email_id' => 'directory-owner@example.test', 'um_mobile_no' => '9876512340', 'um_status' => 2]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => 'Directory Consulting Company',
            'cmpd_description' => 'Business advisory and planning.',
            'cmpd_status' => 1,
            'cmpd_is_document_valid' => '1',
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);
        DB::table('categories_to_companies')->insert(['ctc_cmp_id' => $company->cmp_id, 'ctc_bcm_id' => $category->bcm_id, 'ctc_created_at' => now()]);
        DB::table('business_analytics_events')->insert([
            ['bae_cmp_id' => $company->cmp_id, 'bae_event_type' => 'view', 'bae_source' => 'WEB', 'bae_created_at' => now()],
            ['bae_cmp_id' => $company->cmp_id, 'bae_event_type' => 'view', 'bae_source' => 'WEB', 'bae_created_at' => now()],
            ['bae_cmp_id' => $company->cmp_id, 'bae_event_type' => 'whatsapp', 'bae_source' => 'WEB', 'bae_created_at' => now()],
        ]);

        $response = $this->get(route('members.index', ['search' => 'Directory Consulting']));
        $response->assertOk()
            ->assertSee('Directory Consulting Company')
            ->assertSee($category->name)
            ->assertSee('Highly engaged')
            ->assertSee('data-business-id="'.$company->cmp_id.'"', false)
            ->assertSee(route('business.show', 'directory-consulting-company'), false)
            ->assertDontSee('hidden-owner')
            ->assertDontSee('directory-owner@example.test');
    }

    public function test_inactive_business_page_shows_warning_and_disables_contact_actions(): void
    {
        $owner = UserMaster::create(['um_utm_id' => 2, 'um_user_name' => 'inactive-owner', 'um_email_id' => 'inactive-owner@example.test', 'um_mobile_no' => '9765401234', 'um_status' => 2]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => 'Inactive Sample Business',
            'cmpd_description' => 'Temporarily unavailable business.',
            'cmpd_email' => 'private-inactive@example.test',
            'cmpd_phone' => '9876504321',
            'cmpd_status' => 0,
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);

        $this->actingAs($owner, 'member')
            ->get(route('business.show', 'inactive-sample-business'))
            ->assertOk()
            ->assertSee('Business inactive · Contact and enquiry options are unavailable')
            ->assertSee('Contact and enquiry options will return after activation.')
            ->assertDontSee('private-inactive@example.test')
            ->assertDontSee('9876504321')
            ->assertDontSee('Send enquiry');

        $this->post(route('business.lead', 'inactive-sample-business'), [
            'name' => 'Blocked Lead',
            'phone' => '9234567890',
            'subject' => 'Should not save',
            'message' => 'This inactive business must not receive a lead.',
        ])->assertNotFound();

        $this->get(route('members.index', ['search' => 'Inactive Sample Business']))
            ->assertOk()
            ->assertSee('0 businesses found')
            ->assertDontSee(route('business.show', 'inactive-sample-business'), false);
    }
}
