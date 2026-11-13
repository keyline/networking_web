<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * How complete a business page is, 0–100, matching the editor's "Ready to share?" checklist:
 *   business information (has a category) 20 · about section 20 ·
 *   offerings, gallery photos, videos: 5 points per item, up to 20 each.
 * Four small grouped queries for any number of businesses.
 */
class BusinessCompleteness
{
    public const PER_ITEM = 5;
    public const SECTION_MAX = 20;

    /** @return array<int, int> company id => score */
    public static function scores(array $companyIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $companyIds))));
        if (! $ids) {
            return [];
        }

        $categories = DB::table('categories_to_companies')->whereIn('ctc_cmp_id', $ids)
            ->selectRaw('ctc_cmp_id as id, COUNT(*) as n')->groupBy('ctc_cmp_id')->pluck('n', 'id');
        $about = DB::table('business_portfolios')->whereIn('company_id', $ids)
            ->whereNotNull('about')->whereRaw("TRIM(about) <> ''")->pluck('company_id')->flip();
        $items = DB::table('business_portfolio_items')->whereIn('company_id', $ids)
            ->selectRaw('company_id as id, COUNT(*) as n')->groupBy('company_id')->pluck('n', 'id');
        $media = DB::table('business_portfolio_media')->whereIn('company_id', $ids)
            ->selectRaw("company_id as id, SUM(type = 'image') as photos, SUM(type = 'youtube') as videos")
            ->groupBy('company_id')->get()->keyBy('id');

        $scores = [];
        foreach ($ids as $id) {
            $scores[$id] = self::score(
                (int) ($categories[$id] ?? 0) > 0,
                isset($about[$id]),
                (int) ($items[$id] ?? 0),
                (int) ($media[$id]->photos ?? 0),
                (int) ($media[$id]->videos ?? 0)
            );
        }

        return $scores;
    }

    public static function score(bool $hasCategory, bool $hasAbout, int $offerings, int $photos, int $videos): int
    {
        $part = fn (int $count) => min(self::SECTION_MAX, $count * self::PER_ITEM);

        return ($hasCategory ? 20 : 0) + ($hasAbout ? 20 : 0) + $part($offerings) + $part($photos) + $part($videos);
    }
}
