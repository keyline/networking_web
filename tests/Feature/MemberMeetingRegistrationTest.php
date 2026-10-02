<?php

namespace Tests\Feature;

use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\MemberMeeting;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MemberMeetingRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_business_member_can_register_a_private_one_to_one_meeting(): void
    {
        $reporter = $this->businessMember('meeting-reporter', '9100000101');
        $counterpart = $this->businessMember('meeting-counterpart', '9100000102');

        $this->actingAs($reporter, 'member')->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Register a meeting')
            ->assertSee('Register member meeting')
            ->assertSee('Meeting Counterpart');

        $this->actingAs($reporter, 'member')->post(route('member.meetings.store'), [
            'counterpart_member_id' => $counterpart->um_id,
            'invited_by' => 'other',
            'meeting_at' => '2026-10-01T10:30',
            'mode' => 'in_person',
            'location' => 'Kolkata office',
            'details' => 'Discussed referral opportunities and ways to support each other.',
            'outcome' => 'Exchange two introductions next week.',
            'follow_up_on' => '2026-10-08',
        ])->assertRedirect()->assertSessionHas('success');

        $meeting = MemberMeeting::query()
            ->where('reported_by_um_id', $reporter->um_id)
            ->where('counterpart_um_id', $counterpart->um_id)
            ->where('details', 'Discussed referral opportunities and ways to support each other.')
            ->firstOrFail();
        $this->assertSame($reporter->um_id, $meeting->reported_by_um_id);
        $this->assertSame($counterpart->um_id, $meeting->counterpart_um_id);
        $this->assertSame($counterpart->um_id, $meeting->invited_by_um_id);
        $this->assertSame('2026-10-01 05:00:00', $meeting->meeting_at->format('Y-m-d H:i:s'));

        $this->actingAs($reporter, 'member')->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Recent member meetings')
            ->assertSee('Kolkata office')
            ->assertSee('Exchange two introductions next week.');
    }

    public function test_guest_cannot_register_a_member_meeting(): void
    {
        $guest = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'meeting-guest',
            'um_email_id' => 'meeting-guest@example.test', 'um_mobile_no' => '9100000103', 'um_status' => 2,
        ]);
        $counterpart = $this->businessMember('meeting-member', '9100000104');

        $this->actingAs($guest, 'member')->get(route('dashboard.index'))
            ->assertOk()->assertDontSee('Register a meeting')->assertSee('Find connections');

        $this->actingAs($guest, 'member')->post(route('member.meetings.store'), [
            'counterpart_member_id' => $counterpart->um_id,
            'invited_by' => 'me', 'meeting_at' => '2026-10-01T10:30',
            'mode' => 'online', 'details' => 'Should not be stored.',
        ])->assertForbidden();

        $this->assertDatabaseMissing('member_meetings', [
            'reported_by_um_id' => $guest->um_id,
            'counterpart_um_id' => $counterpart->um_id,
            'details' => 'Should not be stored.',
        ]);
    }

    public function test_meeting_requires_another_active_business_member(): void
    {
        $reporter = $this->businessMember('meeting-validator', '9100000105');
        $guest = UserMaster::create([
            'um_utm_id' => 1, 'um_user_name' => 'not-a-business-member',
            'um_email_id' => 'not-a-business-member@example.test', 'um_mobile_no' => '9100000106', 'um_status' => 2,
        ]);

        $this->actingAs($reporter, 'member')->post(route('member.meetings.store'), [
            'counterpart_member_id' => $guest->um_id,
            'invited_by' => 'me', 'meeting_at' => '2026-10-01T10:30',
            'mode' => 'phone', 'details' => 'Invalid counterpart.',
        ])->assertRedirect()->assertSessionHasErrors('counterpart_member_id')->assertSessionHas('open_dialog', 'meetingModal');

        $this->assertDatabaseMissing('member_meetings', [
            'reported_by_um_id' => $reporter->um_id,
            'counterpart_um_id' => $guest->um_id,
            'details' => 'Invalid counterpart.',
        ]);
    }

    private function businessMember(string $username, string $mobile): UserMaster
    {
        $member = UserMaster::create([
            'um_utm_id' => 2, 'um_user_name' => $username,
            'um_email_id' => $username.'@example.test', 'um_mobile_no' => $mobile, 'um_status' => 2,
        ]);
        UserDetails::create([
            'ud_um_id' => $member->um_id,
            'ud_first_name' => str($username)->replace('-', ' ')->title()->toString(),
        ]);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create([
            'cmpd_cmp_id' => $company->cmp_id,
            'cmpd_name' => str($username)->replace('-', ' ')->title()->append(' Business')->toString(),
            'cmpd_description' => 'Meeting test business.', 'cmpd_status' => 1,
        ]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $member->um_id]);

        return $member;
    }
}
