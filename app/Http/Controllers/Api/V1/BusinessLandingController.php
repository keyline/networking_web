<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BusinessPortfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The published business landing page (/business/{slug}) as JSON, so the
 * mobile app can show the same header image, offerings, gallery, videos and
 * social links. Read-only and public, like the web page itself.
 */
class BusinessLandingController extends Controller
{
    public function show(Request $request)
    {
        if (!hash_equals((string) env('PROJECT_KEY'), (string) $request->header('key'))) {
            $this->response_to_json(false, 'Unauthenticate Request !!!');
        }

        $companyId = (int) $request->input('business_identifier');
        $business = DB::table('companies_details')
            ->where('cmpd_cmp_id', $companyId)
            ->where('cmpd_status', 1)
            ->first();
        if (!$business) {
            $this->response_to_json(false, 'Business Not Found !!!');
        }

        // Only what the owner has published, never the working draft
        $portfolio = BusinessPortfolio::where('company_id', $companyId)
            ->where('is_published', true)
            ->first();
        $page = $portfolio?->published_snapshot ?? [];

        $publicFile = fn (?string $path) => !empty($path) ? url('public/' . ltrim($path, '/')) : null;

        $items = collect($page['items'] ?? [])
            ->sortBy('sort_order')
            ->map(fn ($item) => [
                'type'         => $item['type'] ?? 'product',
                'title'        => $item['title'] ?? '',
                'description'  => $item['description'] ?? '',
                'image'        => $publicFile($item['image'] ?? null),
                'price_label'  => $item['price_label'] ?? null,
                'external_url' => $item['external_url'] ?? null,
            ])->values();

        $media = collect($page['media'] ?? [])->sortBy('sort_order');
        $gallery = $media->where('type', 'image')->map(fn ($image) => [
            'image'   => $publicFile($image['path'] ?? null),
            'title'   => $image['title'] ?? null,
            'caption' => $image['caption'] ?? null,
        ])->values();

        // Businesses without a portfolio gallery still show their older photos
        if ($gallery->isEmpty()) {
            $gallery = DB::table('company_images')
                ->where('ci_cmp_id', $companyId)
                ->where('ci_status', 1)
                ->pluck('ci_image_name')
                ->map(fn ($file) => [
                    'image'   => env('UPLOADS_URL') . 'company/' . $file,
                    'title'   => null,
                    'caption' => null,
                ])->values();
        }

        $videos = $media->where('type', 'youtube')
            ->filter(fn ($video) => preg_match('/^[A-Za-z0-9_-]{11}$/', (string) ($video['youtube_id'] ?? '')))
            ->map(fn ($video) => [
                'youtube_id' => $video['youtube_id'],
                'title'      => $video['title'] ?? null,
                'thumbnail'  => 'https://img.youtube.com/vi/' . $video['youtube_id'] . '/hqdefault.jpg',
                'url'        => 'https://www.youtube.com/watch?v=' . $video['youtube_id'],
            ])->values();

        $social = DB::table('company_sociallink')->where('cs_cmp_id', $companyId)->first();
        $socialLinks = collect([
            'facebook'  => $social->facebook_link ?? null,
            'twitter'   => $social->twitter_link ?? null,
            'instagram' => $social->instagram_link ?? null,
            'linkedin'  => $social->linkedin_link ?? null,
        ])->filter(fn ($link) => !empty($link) && preg_match('#^https?://#i', $link));

        $this->response_to_json(true, 'Data Available !!!', [
            'has_portfolio'        => !empty($page),
            'public_url'           => !empty($business->public_slug) ? url('business/' . $business->public_slug) : null,
            'tagline'              => $page['tagline'] ?? null,
            'about'                => $page['about'] ?? null,
            'hero_image'           => $publicFile($page['hero_image'] ?? null),
            'website'              => $page['website'] ?? null,
            'whatsapp'             => [
                'enabled' => (bool) ($page['whatsapp_enabled'] ?? false),
                'number'  => $page['whatsapp_number'] ?? null,
                'message' => $page['whatsapp_message'] ?? null,
            ],
            'contact_form_enabled' => (bool) ($page['contact_form_enabled'] ?? true),
            'items'                => $items,
            'gallery'              => $gallery,
            'videos'               => $videos,
            'social'               => (object) $socialLinks->all(),
        ]);
    }
}
