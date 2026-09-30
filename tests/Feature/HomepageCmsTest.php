<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\HomepageSection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HomepageCmsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_create_a_section_with_ordered_items(): void
    {
        $admin = Admin::create([
            'company_id' => 0,
            'name' => 'Homepage Admin',
            'type' => 'ma',
            'email' => uniqid('homepage-').'@example.test',
            'login_id' => uniqid('home-'),
            'mobile' => '8'.random_int(100000000, 999999999),
            'password' => Hash::make('password'),
            'status' => 1,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.homepage.store'), [
            'type' => 'testimonials',
            'name' => 'Member testimonials',
            'eyebrow' => 'Member stories',
            'title' => 'What members say',
            'body' => 'Real experiences from our community.',
            'sort_order' => 40,
            'is_active' => 1,
            'items' => [
                ['title' => 'Asha Sen', 'body' => 'The network helped our company find trusted partners.', 'meta_role' => 'Founder, Example Co.'],
                ['title' => 'Raj Das', 'body' => 'We received valuable referrals.', 'meta_role' => 'Consultant'],
            ],
        ]);

        $response->assertRedirect(route('admin.homepage.index'));
        $section = HomepageSection::where('name', 'Member testimonials')->firstOrFail();
        $this->assertTrue($section->is_active);
        $this->assertSame(2, $section->items()->count());
        $this->assertSame('Asha Sen', $section->items()->first()->title);
    }

    public function test_public_homepage_renders_active_sections_and_hides_inactive_sections(): void
    {
        $active = HomepageSection::create([
            'type' => 'testimonials', 'name' => 'Visible stories', 'title' => 'Trusted by local businesses',
            'sort_order' => 41, 'is_active' => true,
        ]);
        $active->items()->create([
            'title' => 'Test Member', 'body' => 'A clean and useful community experience.',
            'meta' => ['role' => 'Business owner'], 'sort_order' => 0, 'is_active' => true,
        ]);
        HomepageSection::create([
            'type' => 'content', 'name' => 'Hidden block', 'title' => 'This must stay hidden',
            'sort_order' => 42, 'is_active' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Trusted by local businesses')
            ->assertSee('A clean and useful community experience.')
            ->assertDontSee('This must stay hidden');
    }
}
