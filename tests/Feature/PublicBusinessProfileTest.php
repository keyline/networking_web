<?php

namespace Tests\Feature;

use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
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

    public function test_anyone_can_view_active_business_and_send_a_lead(): void
    {
        Notification::fake();
        $owner = UserMaster::create(['um_utm_id' => 2, 'um_user_name' => 'EN000001', 'um_email_id' => 'owner@example.test', 'um_mobile_no' => '9876543210', 'um_status' => 2]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create(['cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Example Business', 'public_slug' => 'example-business', 'cmpd_description' => 'A useful company.', 'cmpd_status' => 1, 'cmpd_is_document_valid' => '1']);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);

        $this->get(route('business.show', 'example-business'))->assertOk()->assertSee('Example Business')->assertSee('Send enquiry');

        $response = $this->post(route('business.lead', 'example-business'), [
            'name' => 'Prospective Customer', 'email' => 'lead@example.test', 'phone' => '9123456789',
            'subject' => 'Need a quotation', 'message' => 'Please contact me with your pricing.',
        ]);

        $response->assertRedirect(route('business.show', 'example-business'))->assertSessionHas('lead_success');
        $this->assertDatabaseHas('enquiry_master', ['enm_email' => 'lead@example.test', 'enm_subject' => 'Need a quotation']);
        $this->assertDatabaseHas('enquiry_to_user', ['etu_cmp_id' => $company->cmp_id, 'etu_um_id' => $owner->um_id]);
        Notification::assertSentTo($owner, BusinessLeadSmsNotification::class);
        Notification::assertSentOnDemand(BusinessLeadEmailNotification::class);
    }
}
