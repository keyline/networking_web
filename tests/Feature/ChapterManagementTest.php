<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\ChapterMember;
use Tests\TestCase;

class ChapterManagementTest extends TestCase
{
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
}
