<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Page;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminLegalPagesTest extends TestCase
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

    public function test_settings_page_shows_legal_pages_editor(): void
    {
        // The settings action echoes its HTML rather than returning it
        ob_start();
        $this->signInAsAdmin()->get('admin/settings')->assertOk();
        $html = ob_get_clean();

        $this->assertStringContainsString('Legal Pages', $html);
        $this->assertStringContainsString('name="privacy_content"', $html);
        $this->assertStringContainsString('name="terms_content"', $html);
        $this->assertStringContainsString(url('admin/legal-pages-settings'), $html);
    }

    public function test_admin_saves_sanitized_content_shown_on_website(): void
    {
        $this->signInAsAdmin()->post('admin/legal-pages-settings', [
            'privacy_content' => '<h2>Your data</h2><p onclick="steal()">We keep it safe.</p><script>alert(1)</script>',
            'terms_content' => '<p>Be <strong>nice</strong>. <a href="javascript:alert(1)">x</a></p>',
        ])->assertRedirect('admin/settings#tab13');

        $privacy = Page::where('page_slug', 'privacy-policy')->firstOrFail();
        $this->assertSame('<h2>Your data</h2><p>We keep it safe.</p>', $privacy->page_content);
        $this->assertSame(1, (int) $privacy->status);
        $this->assertSame('<p>Be <strong>nice</strong>. <a>x</a></p>',
            Page::where('page_slug', 'terms-conditions')->value('page_content'));

        $this->get('page/privacy-policy')->assertOk()->assertSee('We keep it safe.');
    }

    public function test_footer_links_to_both_legal_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(url('page/privacy-policy'), false)
            ->assertSee(url('page/terms-conditions'), false);
    }

    public function test_guests_cannot_update_legal_pages(): void
    {
        $before = Page::where('page_slug', 'privacy-policy')->value('page_content');

        $this->post('admin/legal-pages-settings', ['privacy_content' => '<p>hacked</p>']);

        $this->assertSame($before, Page::where('page_slug', 'privacy-policy')->value('page_content'));
    }
}
