<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\ChapterMember;
use App\Models\Admin;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChapterManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_chapter_model_exposes_management_attributes_and_relationships(): void
    {
        $chapter = new Chapter(['name' => 'Central', 'code' => 'NW-CEN', 'status' => 'active']);
        $this->assertSame('Central', $chapter->name);
        $this->assertSame('NW-CEN', $chapter->code);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $chapter->members());
    }

    public function test_admin_chapter_routes_are_registered(): void
    {
        $this->assertSame('/admin/chapters', route('admin.chapters.index', absolute: false));
        $chapter = new Chapter(['id' => 9]);
        $chapter->exists = true;
        $this->assertSame('/admin/chapters/9', route('admin.chapters.show', $chapter, false));
    }

    public function test_member_role_label_is_readable(): void
    {
        $member = new ChapterMember(['role' => 'secretary_treasurer']);
        $this->assertSame('Secretary Treasurer', $member->role_label);
    }

    public function test_regular_meeting_label_supports_flexible_schedules(): void
    {
        $weekly = new Chapter(['meeting_frequency' => 'weekly', 'meeting_day' => 'Wednesday', 'meeting_time' => '18:30:00']);
        $monthly = new Chapter(['meeting_frequency' => 'monthly', 'meeting_day_of_month' => 12, 'meeting_time' => '09:00:00']);
        $twiceMonthly = new Chapter(['meeting_frequency' => 'twice_monthly', 'meeting_day_of_month' => 5, 'meeting_second_day_of_month' => 20, 'meeting_time' => '17:15:00']);

        $this->assertSame('Weekly on Wednesday at 06:30 PM', $weekly->regular_meeting_label);
        $this->assertSame('Monthly on day 12 at 09:00 AM', $monthly->regular_meeting_label);
        $this->assertSame('Twice monthly on days 5 and 20 at 05:15 PM', $twiceMonthly->regular_meeting_label);
    }

    public function test_admin_can_save_an_online_monthly_chapter_meeting(): void
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

        $chapter = Chapter::create(['name' => 'Flexible Meeting Chapter', 'code' => 'FLEX-MEET-CH', 'status' => 'forming']);

        $this->put(route('admin.chapters.update', $chapter), [
            'name' => 'Flexible Meeting Chapter',
            'code' => 'FLEX-MEET-CH',
            'city' => 'Kolkata',
            'meeting_frequency' => 'monthly',
            'meeting_day_of_month' => 12,
            'meeting_time' => '18:30',
            'meeting_mode' => 'online',
            'meeting_link' => 'https://meet.example.com/flexible-chapter',
            'meeting_address' => 'This must be cleared for an online meeting',
            'status' => 'active',
        ])->assertRedirect(route('admin.chapters.show', $chapter));

        $chapter->refresh();
        $this->assertSame('monthly', $chapter->meeting_frequency);
        $this->assertSame(12, (int) $chapter->meeting_day_of_month);
        $this->assertSame('online', $chapter->meeting_mode);
        $this->assertSame('https://meet.example.com/flexible-chapter', $chapter->meeting_link);
        $this->assertNull($chapter->meeting_address);
        $this->assertNull($chapter->venue);

        $form = view('admin.maincontents.chapters.form', ['chapter' => $chapter])->render();
        $this->assertStringContainsString('<select class="form-select" name="meeting_day_of_month">', $form);
        $this->assertStringContainsString('<select class="form-select" name="meeting_second_day_of_month">', $form);
        $this->assertStringContainsString('<option value="12" selected>Day 12</option>', $form);
    }

    public function test_chapter_membership_is_user_based_and_displays_all_linked_businesses(): void
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

        $chapter = Chapter::create(['name' => 'User Business Chapter', 'code' => 'USER-BIZ-CH', 'status' => 'active']);
        $user = UserMaster::create([
            'um_utm_id' => 2,
            'um_user_name' => 'chapter-multi-business',
            'um_email_id' => 'chapter-multi-business@example.test',
            'um_mobile_no' => '9876598765',
            'um_status' => 2,
        ]);
        $firstCompany = CompaniesMaster::create([]);
        $secondCompany = CompaniesMaster::create([]);
        DB::table('companies_details')->insert([
            ['cmpd_cmp_id' => $firstCompany->cmp_id, 'cmpd_name' => 'Chapter First Business', 'cmpd_description' => 'First linked business.', 'cmpd_status' => 1],
            ['cmpd_cmp_id' => $secondCompany->cmp_id, 'cmpd_name' => 'Chapter Second Business', 'cmpd_description' => 'Second linked business.', 'cmpd_status' => 1],
        ]);
        DB::table('user_companies_map')->insert([
            ['ucm_um_id' => $user->um_id, 'ucm_cmp_id' => $firstCompany->cmp_id],
            ['ucm_um_id' => $user->um_id, 'ucm_cmp_id' => $secondCompany->cmp_id],
        ]);

        $this->post(route('admin.chapters.members.store', $chapter), [
            'user_id' => $user->um_id,
            'company_id' => $firstCompany->cmp_id,
            'role' => 'member',
            'status' => 'active',
            'joined_on' => now()->toDateString(),
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('chapter_members', [
            'chapter_id' => $chapter->id,
            'user_id' => $user->um_id,
            'company_id' => null,
        ]);

        $chapter->loadCount(['members', 'activeMembers']);
        $members = $chapter->members()
            ->with(['user.userDetail', 'user.companies.details'])
            ->paginate(30);
        $html = view('admin.maincontents.chapters.show', [
            'chapter' => $chapter,
            'members' => $members,
            'availableUsers' => collect(),
        ])->render();

        $this->assertStringNotContainsString('Represented business', $html);
        $this->assertStringContainsString('All businesses linked to the selected user will be included automatically.', $html);
        $this->assertStringContainsString('Chapter First Business', $html);
        $this->assertStringContainsString('Chapter Second Business', $html);
    }
}
