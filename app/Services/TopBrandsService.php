<?php

namespace App\Services;

use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ranks active businesses for the app's "Top Brands" grid using the rule
 * chosen in admin Settings > Top Brands.
 */
class TopBrandsService
{
    public const METRICS = [
        'visits'              => 'Most profile visits',
        'references_received' => 'Most references received',
        'references_given'    => 'Most references given',
        'enquiries'           => 'Most enquiries',
        'alphabetical'        => 'Alphabetical (A–Z)',
    ];

    public const PERIODS = [7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days', 0 => 'All time'];

    /** @return array{metric: string, days: int, limit: int} */
    public static function settings(): array
    {
        $defaults = ['metric' => 'alphabetical', 'days' => 30, 'limit' => 12];
        // Before the migration runs the columns do not exist yet
        if (!Schema::hasColumn('general_settings', 'top_brands_metric')) {
            return $defaults;
        }
        $row = GeneralSetting::find(1);
        return [
            'metric' => array_key_exists($row->top_brands_metric ?? '', self::METRICS) ? $row->top_brands_metric : $defaults['metric'],
            'days'   => (int) ($row->top_brands_days ?? $defaults['days']),
            'limit'  => max(1, (int) ($row->top_brands_limit ?? $defaults['limit'])),
        ];
    }

    /**
     * Company ids (cmpd_cmp_id) in display order. Businesses with activity in
     * the period come first; the rest of the grid is filled alphabetically so
     * it is never half empty.
     */
    public function rankedCompanyIds(): array
    {
        $settings = self::settings();
        $from = $settings['days'] > 0 ? Carbon::today()->subDays($settings['days'] - 1) : null;

        $activeByName = DB::table('companies_details')
            ->where('cmpd_status', 1)
            ->orderBy('cmpd_name')
            ->pluck('cmpd_cmp_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $scores = $this->scores($settings['metric'], $from);
        $ranked = array_values(array_filter(
            array_keys($scores),
            fn ($id) => in_array($id, $activeByName, true)
        ));

        $ordered = array_values(array_unique(array_merge($ranked, $activeByName)));
        return array_slice($ordered, 0, $settings['limit']);
    }

    /** @return array<int, int> company id => score, highest first */
    private function scores(string $metric, ?Carbon $from): array
    {
        switch ($metric) {
            case 'visits':
                if (!Schema::hasTable('business_analytics_events')) {
                    return [];
                }
                $query = DB::table('business_analytics_events')
                    ->where('bae_event_type', 'view')
                    ->when($from, fn ($q) => $q->where('bae_created_at', '>=', $from))
                    ->groupBy('bae_cmp_id')
                    ->selectRaw('bae_cmp_id as company_id, COUNT(*) as score');
                break;

            case 'references_received':
            case 'enquiries':
                $query = DB::table('enquiry_to_user')
                    ->join('enquiry_master', 'enquiry_master.enm_id', '=', 'enquiry_to_user.etu_enm_id')
                    ->where('enquiry_to_user.etu_cmp_id', '>', 0)
                    ->when($metric === 'references_received', fn ($q) => $q->where('enquiry_master.enm_is_myself', 0))
                    ->when($from, fn ($q) => $q->where('enquiry_master.enm_created_at', '>=', $from))
                    ->groupBy('enquiry_to_user.etu_cmp_id')
                    ->selectRaw('enquiry_to_user.etu_cmp_id as company_id, COUNT(*) as score');
                break;

            case 'references_given':
                // Leads a business owner passed to other businesses, credited
                // to each business that owner runs
                $query = DB::table('enquiry_to_user')
                    ->join('enquiry_master', 'enquiry_master.enm_id', '=', 'enquiry_to_user.etu_enm_id')
                    ->join('user_companies_map', 'user_companies_map.ucm_um_id', '=', 'enquiry_to_user.etu_um_id')
                    ->where('enquiry_to_user.etu_cmp_id', '>', 0)
                    ->whereColumn('enquiry_to_user.etu_cmp_id', '!=', 'user_companies_map.ucm_cmp_id')
                    ->where('enquiry_master.enm_is_myself', 0)
                    ->when($from, fn ($q) => $q->where('enquiry_master.enm_created_at', '>=', $from))
                    ->groupBy('user_companies_map.ucm_cmp_id')
                    ->selectRaw('user_companies_map.ucm_cmp_id as company_id, COUNT(*) as score');
                break;

            default:
                return [];
        }

        return $query->orderByDesc('score')->get()
            ->mapWithKeys(fn ($row) => [(int) $row->company_id => (int) $row->score])
            ->all();
    }
}
