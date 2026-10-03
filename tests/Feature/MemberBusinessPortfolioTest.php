<?php

namespace Tests\Feature;

use App\Models\BusinessPortfolio;
use App\Models\BusinessPortfolioItem;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\MemberMembership;
use App\Models\User\UserMaster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MemberBusinessPortfolioTest extends TestCase
{
    use DatabaseTransactions;

    public function test_owner_can_manage_and_publish_portfolio_but_another_member_cannot(): void
    {
        [$owner, $company] = $this->business('portfolio-owner');
        $other = UserMaster::create(['um_utm_id' => 3, 'um_user_name' => 'other', 'um_email_id' => 'other@example.test', 'um_mobile_no' => '9000000002', 'um_status' => 2]);
        $token = $company->portfolioRouteToken();
        $category = BusinessCategoryMaster::where('status', 1)->firstOrFail();

        $this->assertStringNotContainsString('/'.$company->cmp_id.'/portfolio', route('member.portfolio.edit', $token));
        $this->actingAs($owner, 'member')->get(route('member.portfolio.edit', $token))
            ->assertOk()
            ->assertSeeText('Business identity')
            ->assertSee('Search categories')
            ->assertSee('Choose up to three categories')
            ->assertSee('data-mini-editor', false)
            ->assertSee('data-command="bold"', false)
            ->assertSee('data-command="italic"', false)
            ->assertSee('data-command="insertUnorderedList"', false);
        $this->actingAs($other, 'member')->get(route('member.portfolio.edit', $token))->assertForbidden();
        $this->actingAs($owner, 'member')->get(route('member.portfolio.edit', '192'))->assertNotFound();

        $this->actingAs($owner, 'member')->put(route('member.portfolio.update', $token), [
            'business_name' => 'Updated Business Name', 'category_ids' => [$category->bcm_id],
            'business_email' => 'business@example.test', 'business_phone' => '9876543210',
            'business_whatsapp' => '9876543210', 'gst_number' => '19ABCDE1234F1Z5',
            'address1' => '10 Example Street', 'pincode' => '700001',
            'tagline' => 'Trusted business services',
            'about' => '<p>A <strong onclick="alert(1)">complete</strong> business profile.</p><ul><li>Trusted advice</li></ul><script>alert("unsafe")</script>',
            'website' => 'https://example.test', 'whatsapp_enabled' => 1, 'contact_form_enabled' => 1,
        ])->assertRedirect(route('member.portfolio.edit', $token).'#profile');
        $this->actingAs($owner, 'member')->post(route('member.portfolio.items.store', $token), [
            'type' => 'service', 'title' => 'Business consulting', 'description' => 'Practical advice.', 'price_label' => 'Ask for price',
        ])->assertRedirect(route('member.portfolio.edit', $token).'#offerings');
        $this->actingAs($owner, 'member')->post(route('member.portfolio.videos.store', $token), [
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ', 'title' => 'Introduction',
        ])->assertRedirect(route('member.portfolio.edit', $token).'#videos');
        $this->actingAs($owner, 'member')->post(route('member.portfolio.publish', $token))->assertRedirect();

        $portfolio = BusinessPortfolio::where('company_id', $company->cmp_id)->firstOrFail();
        $this->assertTrue($portfolio->is_published);
        $this->assertNotNull($portfolio->published_at);
        $this->assertStringContainsString('<strong>complete</strong>', $portfolio->about);
        $this->assertStringContainsString('<li>Trusted advice</li>', $portfolio->about);
        $this->assertStringNotContainsString('<script', $portfolio->about);
        $this->assertStringNotContainsString('onclick', $portfolio->about);
        $this->assertStringNotContainsString('unsafe', $portfolio->about);
        $this->assertSame('Trusted business services', $portfolio->published_snapshot['tagline']);
        $this->assertDatabaseHas('companies_details', ['cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => 'Updated Business Name', 'cmpd_gst_no' => '19ABCDE1234F1Z5']);
        $this->assertDatabaseHas('categories_to_companies', ['ctc_cmp_id' => $company->cmp_id, 'ctc_bcm_id' => $category->bcm_id]);
        $this->actingAs($owner, 'member')->get(route('member.portfolio.edit', $token))
            ->assertOk()
            ->assertSee('Last published')
            ->assertSee($portfolio->published_at->timezone('Asia/Kolkata')->format('d M Y, h:i A'));
        $this->get(route('business.show', 'updated-business-name'))
            ->assertOk()
            ->assertSee('Business consulting')
            ->assertSee('<strong>complete</strong>', false)
            ->assertSee('<li>Trusted advice</li>', false)
            ->assertDontSee('unsafe')
            ->assertSee('youtube-nocookie.com', false);
    }

    public function test_member_can_select_no_more_than_three_business_categories(): void
    {
        [$owner, $company] = $this->business('three-category-limit');
        $categoryIds = BusinessCategoryMaster::where('status', 1)->orderBy('bcm_id')->limit(4)->pluck('bcm_id')->all();
        $this->assertCount(4, $categoryIds);

        $this->actingAs($owner, 'member')->put(route('member.portfolio.update', $company->portfolioRouteToken()), [
            'business_name' => 'Three Category Limit',
            'category_ids' => $categoryIds,
        ])->assertSessionHasErrors('category_ids');
    }

    public function test_owner_can_edit_and_activate_or_deactivate_an_offering(): void
    {
        [$owner, $company] = $this->business('editable-offering');
        $token = $company->portfolioRouteToken();

        $this->actingAs($owner, 'member')->post(route('member.portfolio.items.store', $token), [
            'type' => 'service',
            'title' => 'Original service',
            'description' => 'Original description.',
            'price_label' => 'Ask for price',
        ])->assertRedirect(route('member.portfolio.edit', $token).'#offerings');

        $item = BusinessPortfolioItem::where('company_id', $company->cmp_id)->firstOrFail();
        $this->actingAs($owner, 'member')->get(route('member.portfolio.edit', $token))
            ->assertOk()
            ->assertSee('Edit')
            ->assertSee('Active')
            ->assertSee(route('member.portfolio.items.update', [$token, $item]), false)
            ->assertSee(route('member.portfolio.items.toggle', [$token, $item]), false);

        $this->actingAs($owner, 'member')->put(route('member.portfolio.items.update', [$token, $item]), [
            'type' => 'product',
            'title' => 'Updated product',
            'description' => 'Updated description.',
            'price_label' => '₹2,500',
            'external_url' => 'https://example.test/product',
        ])->assertRedirect(route('member.portfolio.edit', $token).'#offerings');

        $this->assertDatabaseHas('business_portfolio_items', [
            'id' => $item->id,
            'type' => 'product',
            'title' => 'Updated product',
            'is_active' => 1,
        ]);

        $this->actingAs($owner, 'member')
            ->patch(route('member.portfolio.items.toggle', [$token, $item]))
            ->assertRedirect(route('member.portfolio.edit', $token).'#offerings');
        $this->assertDatabaseHas('business_portfolio_items', ['id' => $item->id, 'is_active' => 0]);

        $this->actingAs($owner, 'member')->post(route('member.portfolio.publish', $token))->assertRedirect();
        $portfolio = BusinessPortfolio::where('company_id', $company->cmp_id)->firstOrFail();
        $this->assertSame([], $portfolio->published_snapshot['items']);
        $this->get(route('business.show', 'editable-offering'))->assertOk()->assertDontSee('Updated product');

        $this->actingAs($owner, 'member')->patch(route('member.portfolio.items.toggle', [$token, $item]));
        $this->actingAs($owner, 'member')->post(route('member.portfolio.publish', $token));
        $this->get(route('business.show', 'editable-offering'))->assertOk()->assertSee('Updated product');
    }

    public function test_gallery_upload_is_compressed_below_two_hundred_kilobytes(): void
    {
        [$owner, $company] = $this->business('compressed-gallery');
        $token = $company->portfolioRouteToken();
        $response = $this->actingAs($owner, 'member')->post(route('member.portfolio.images.store', $token), [
            'image' => UploadedFile::fake()->image('large.jpg', 2400, 1800), 'title' => 'Completed project', 'caption' => 'Our work',
        ]);
        $response->assertRedirect(route('member.portfolio.edit', $token).'#gallery');
        $path = DB::table('business_portfolio_media')->where('company_id', $company->cmp_id)->value('path');
        $this->assertNotNull($path);
        $this->assertLessThanOrEqual(204800, filesize(public_path($path)));
        @unlink(public_path($path));
    }

    public function test_owner_can_upload_a_hero_image_and_sees_image_guidance(): void
    {
        [$owner, $company] = $this->business('hero-image-upload');
        $token = $company->portfolioRouteToken();
        $category = BusinessCategoryMaster::where('status', 1)->firstOrFail();

        $this->actingAs($owner, 'member')->get(route('member.portfolio.edit', $token))
            ->assertOk()
            ->assertSee('Recommended: 1600 × 600 px landscape (8:3).')
            ->assertSee('maximum 10 MB');

        $this->actingAs($owner, 'member')->put(route('member.portfolio.update', $token), [
            'business_name' => 'Hero Image Upload',
            'category_ids' => [$category->bcm_id],
            'hero_image' => UploadedFile::fake()->image('hero.jpg', 2400, 900),
        ])->assertRedirect(route('member.portfolio.edit', $token).'#profile');

        $path = BusinessPortfolio::where('company_id', $company->cmp_id)->value('hero_image');
        $this->assertNotNull($path);
        $this->assertFileExists(public_path($path));
        $this->assertLessThanOrEqual(204800, filesize(public_path($path)));
        @unlink(public_path($path));
    }

    private function business(string $slug): array
    {
        $owner = UserMaster::create(['um_utm_id' => 2, 'um_user_name' => $slug, 'um_email_id' => $slug.'@example.test', 'um_mobile_no' => '9000000001', 'um_status' => 2]);
        MemberMembership::create(['user_id' => $owner->um_id, 'registration_date' => today(), 'renewal_date' => today()->addYear(), 'payment_date' => today(), 'payment_amount' => 1, 'membership_status' => 'active']);
        $company = CompaniesMaster::create([]);
        CompaniesDetail::create(['cmpd_cmp_id' => $company->cmp_id, 'cmpd_name' => ucfirst($slug), 'public_slug' => $slug, 'cmpd_description' => 'Test business description.', 'cmpd_status' => 1]);
        DB::table('user_companies_map')->insert(['ucm_cmp_id' => $company->cmp_id, 'ucm_um_id' => $owner->um_id]);
        return [$owner, $company];
    }
}
