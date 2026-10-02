<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Libraries\JWT;
use App\Models\UserDevice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Business owner analytics for the mobile app dashboard.
 *
 * track   - records a view / contact tap on a business profile
 * summary - totals, previous-period comparison, daily trend and recent
 *           enquiries for one business the logged-in user owns
 */
class BusinessAnalyticsController extends Controller
{
    private const EVENT_TYPES = ['view', 'call', 'whatsapp', 'email', 'enquiry', 'share'];
    private const CLICK_TYPES = ['call', 'whatsapp', 'email', 'share'];
    /** 0 means "all time". */
    private const ALLOWED_RANGES = [0, 7, 30, 90];

    /** Repeat events from the same visitor inside this window count once. */
    private const DEDUP_MINUTES = 30;

    public function track(Request $request)
    {
        $headerData = $request->header();
        if (($headerData['key'][0] ?? null) != env('PROJECT_KEY')) {
            $this->response_to_json(false, 'Unauthenticate Request !!!');
        }

        $companyId = (int) $request->input('business_identifier');
        $eventType = strtolower((string) $request->input('event_type'));
        if ($companyId <= 0 || !in_array($eventType, self::EVENT_TYPES, true)) {
            $this->response_to_json(false, 'Invalid business or event type !!!');
        }

        $companyExists = DB::table('companies_details')->where('cmpd_cmp_id', $companyId)->exists();
        if (!$companyExists) {
            $this->response_to_json(false, 'Business Not Found !!!');
        }

        // Tracking also works without a login; the token only identifies the visitor
        $userId = null;
        $token  = $headerData['authorization'][0] ?? null;
        if ($token) {
            $auth   = $this->tokenAuth($token);
            $userId = $auth['status'] ? (int) $auth['data'][1] : null;
        }

        // Owners looking at their own business should not inflate their numbers
        if ($userId && $this->ownsBusiness($userId, $companyId)) {
            $this->response_to_json(true, 'Own business, not tracked');
        }

        $deviceId = substr((string) $request->input('device_id', ''), 0, 100) ?: null;
        $source   = strtoupper(substr((string) ($headerData['source'][0] ?? ''), 0, 20)) ?: null;

        $recentDuplicate = DB::table('business_analytics_events')
            ->where('bae_cmp_id', $companyId)
            ->where('bae_event_type', $eventType)
            ->where('bae_um_id', $userId)
            ->where('bae_device_id', $deviceId)
            ->where('bae_created_at', '>=', Carbon::now()->subMinutes(self::DEDUP_MINUTES))
            ->exists();

        if (!$recentDuplicate) {
            DB::table('business_analytics_events')->insert([
                'bae_cmp_id'     => $companyId,
                'bae_event_type' => $eventType,
                'bae_um_id'      => $userId,
                'bae_device_id'  => $deviceId,
                'bae_source'     => $source,
                'bae_created_at' => Carbon::now(),
            ]);
        }

        $this->response_to_json(true, 'Tracked');
    }

    public function summary(Request $request)
    {
        $headerData = $request->header();
        if (($headerData['key'][0] ?? null) != env('PROJECT_KEY')) {
            $this->response_to_json(false, 'Unauthenticate Request !!!');
        }

        $auth = $this->tokenAuth($headerData['authorization'][0] ?? null);
        if (!$auth['status']) {
            $this->response_to_json(false, $auth['data']);
        }
        $userId = (int) $auth['data'][1];

        $companyId = (int) $request->input('business_identifier');
        if (!$this->ownsBusiness($userId, $companyId)) {
            $this->response_to_json(false, 'Business Not Found !!!');
        }

        $rangeDays = (int) $request->input('range_days', 30);
        if (!in_array($rangeDays, self::ALLOWED_RANGES, true)) {
            $rangeDays = 30;
        }

        $allTime     = $rangeDays === 0;
        $periodEnd   = Carbon::now();
        $periodStart = $allTime
            ? Carbon::create(2000, 1, 1)
            : Carbon::today()->subDays($rangeDays - 1);
        $previousStart = $allTime ? null : $periodStart->copy()->subDays($rangeDays);

        $details = DB::table('companies_details')->where('cmpd_cmp_id', $companyId)->first();
        $category = DB::table('business_category_master')
            ->join('categories_to_companies', 'business_category_master.bcm_id', '=', 'categories_to_companies.ctc_bcm_id')
            ->where('categories_to_companies.ctc_cmp_id', $companyId)
            ->value('business_category_master.name');
        $rating = getBusinessRating($companyId);

        $this->response_to_json(true, 'Data Available !!!', [
            'business' => [
                'id'       => $companyId,
                'name'     => $details->cmpd_name ?? '',
                'logo'     => (!empty($details->cmpd_logo))
                    ? env('UPLOADS_URL') . 'company/' . $details->cmpd_logo
                    : env('UPLOADS_URL') . 'company/' . env('NO_IMAGE'),
                'category' => $category ?? 'Unclassified',
                'status'   => (int) ($details->cmpd_status ?? 0),
            ],
            'range_days'       => $rangeDays,
            'totals'           => $this->periodTotals($companyId, $userId, $periodStart, $periodEnd),
            // No earlier period to compare "all time" against
            'previous'         => $allTime ? null : $this->periodTotals($companyId, $userId, $previousStart, $periodStart),
            'rating'           => [
                'avg'   => round((float) $rating->avg_rating, 1),
                'total' => (int) $rating->total_reviews,
            ],
            'trend_unit'       => $allTime ? 'month' : 'day',
            'trend'            => $allTime
                ? $this->monthlyTrend($companyId, 12)
                : $this->dailyTrend($companyId, $periodStart, $rangeDays),
            'top_referrers'    => $this->topReferrers($companyId, $periodStart, $periodEnd),
            'recent_enquiries' => $this->recentEnquiries($companyId),
            'recent_interactions' => $this->recentInteractions($companyId),
        ]);
    }

    private function periodTotals(int $companyId, int $ownerId, Carbon $from, Carbon $to): array
    {
        $events = DB::table('business_analytics_events')
            ->where('bae_cmp_id', $companyId)
            ->where('bae_created_at', '>=', $from)
            ->where('bae_created_at', '<', $to)
            ->select('bae_event_type', DB::raw('COUNT(*) as total'))
            ->groupBy('bae_event_type')
            ->pluck('total', 'bae_event_type');

        $uniqueVisitors = DB::table('business_analytics_events')
            ->where('bae_cmp_id', $companyId)
            ->where('bae_event_type', 'view')
            ->where('bae_created_at', '>=', $from)
            ->where('bae_created_at', '<', $to)
            ->distinct()
            ->count(DB::raw("CONCAT(COALESCE(bae_um_id, 0), '-', COALESCE(bae_device_id, ''))"));

        // A reference is an enquiry someone sent on behalf of another person
        $enquiries = DB::table('enquiry_to_user')
            ->join('enquiry_master', 'enquiry_to_user.etu_enm_id', '=', 'enquiry_master.enm_id')
            ->where('enquiry_to_user.etu_cmp_id', $companyId)
            ->where('enquiry_master.enm_created_at', '>=', $from)
            ->where('enquiry_master.enm_created_at', '<', $to)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(enquiry_master.enm_is_myself = 0), 0) as references_count')
            ->first();

        // Leads this owner passed to other businesses (BNI "references given")
        $referencesGiven = DB::table('enquiry_to_user')
            ->join('enquiry_master', 'enquiry_to_user.etu_enm_id', '=', 'enquiry_master.enm_id')
            ->where('enquiry_to_user.etu_um_id', $ownerId)
            ->where('enquiry_to_user.etu_cmp_id', '>', 0)
            ->where('enquiry_master.enm_is_myself', 0)
            ->where('enquiry_master.enm_created_at', '>=', $from)
            ->where('enquiry_master.enm_created_at', '<', $to)
            ->count();

        $clicks = 0;
        foreach (self::CLICK_TYPES as $type) {
            $clicks += (int) ($events[$type] ?? 0);
        }

        return [
            'views'           => (int) ($events['view'] ?? 0),
            'unique_visitors' => (int) $uniqueVisitors,
            'clicks'          => $clicks,
            'calls'           => (int) ($events['call'] ?? 0),
            'whatsapp'        => (int) ($events['whatsapp'] ?? 0),
            'emails'          => (int) ($events['email'] ?? 0),
            'shares'          => (int) ($events['share'] ?? 0),
            'enquiries'        => (int) ($enquiries->total ?? 0),
            'references'       => (int) ($enquiries->references_count ?? 0),
            'direct_enquiries' => (int) ($enquiries->total ?? 0) - (int) ($enquiries->references_count ?? 0),
            'references_given' => (int) $referencesGiven,
        ];
    }

    private function dailyTrend(int $companyId, Carbon $from, int $rangeDays): array
    {
        $events = DB::table('business_analytics_events')
            ->where('bae_cmp_id', $companyId)
            ->where('bae_created_at', '>=', $from)
            ->selectRaw("DATE(bae_created_at) as day, SUM(bae_event_type = 'view') as views, SUM(bae_event_type IN ('call','whatsapp','email','share')) as clicks")
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $enquiries = DB::table('enquiry_to_user')
            ->join('enquiry_master', 'enquiry_to_user.etu_enm_id', '=', 'enquiry_master.enm_id')
            ->where('enquiry_to_user.etu_cmp_id', $companyId)
            ->where('enquiry_master.enm_created_at', '>=', $from)
            ->selectRaw('DATE(enquiry_master.enm_created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $trend = [];
        for ($i = 0; $i < $rangeDays; $i++) {
            $day = $from->copy()->addDays($i)->toDateString();
            $trend[] = [
                'date'      => $day,
                'views'     => (int) ($events[$day]->views ?? 0),
                'clicks'    => (int) ($events[$day]->clicks ?? 0),
                'enquiries' => (int) ($enquiries[$day] ?? 0),
            ];
        }

        return $trend;
    }

    private function monthlyTrend(int $companyId, int $months): array
    {
        $from = Carbon::today()->startOfMonth()->subMonths($months - 1);

        $events = DB::table('business_analytics_events')
            ->where('bae_cmp_id', $companyId)
            ->where('bae_created_at', '>=', $from)
            ->selectRaw("DATE_FORMAT(bae_created_at, '%Y-%m-01') as month, SUM(bae_event_type = 'view') as views, SUM(bae_event_type IN ('call','whatsapp','email','share')) as clicks")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $enquiries = DB::table('enquiry_to_user')
            ->join('enquiry_master', 'enquiry_to_user.etu_enm_id', '=', 'enquiry_master.enm_id')
            ->where('enquiry_to_user.etu_cmp_id', $companyId)
            ->where('enquiry_master.enm_created_at', '>=', $from)
            ->selectRaw("DATE_FORMAT(enquiry_master.enm_created_at, '%Y-%m-01') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $trend = [];
        for ($i = 0; $i < $months; $i++) {
            $month = $from->copy()->addMonths($i)->toDateString();
            $trend[] = [
                'date'      => $month,
                'views'     => (int) ($events[$month]->views ?? 0),
                'clicks'    => (int) ($events[$month]->clicks ?? 0),
                'enquiries' => (int) ($enquiries[$month] ?? 0),
            ];
        }

        return $trend;
    }

    /** Members who referred the most leads to this business. */
    private function topReferrers(int $companyId, Carbon $from, Carbon $to): array
    {
        return DB::table('enquiry_to_user')
            ->join('enquiry_master', 'enquiry_to_user.etu_enm_id', '=', 'enquiry_master.enm_id')
            ->join('user_details', 'enquiry_to_user.etu_um_id', '=', 'user_details.ud_um_id')
            ->where('enquiry_to_user.etu_cmp_id', $companyId)
            ->where('enquiry_master.enm_is_myself', 0)
            ->where('enquiry_master.enm_created_at', '>=', $from)
            ->where('enquiry_master.enm_created_at', '<', $to)
            ->groupBy('enquiry_to_user.etu_um_id', 'user_details.ud_first_name', 'user_details.ud_last_name')
            ->orderByDesc('total')
            ->limit(3)
            ->selectRaw('user_details.ud_first_name as first_name, user_details.ud_last_name as last_name, COUNT(*) as total')
            ->get()
            ->map(fn ($row) => [
                'name'  => trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) ?: 'Member',
                'count' => (int) $row->total,
            ])
            ->all();
    }

    private function recentEnquiries(int $companyId): array
    {
        return DB::table('enquiry_to_user')
            ->join('enquiry_master', 'enquiry_to_user.etu_enm_id', '=', 'enquiry_master.enm_id')
            ->where('enquiry_to_user.etu_cmp_id', $companyId)
            ->orderByDesc('enquiry_master.enm_id')
            ->limit(5)
            ->get([
                'enquiry_master.enm_id',
                'enquiry_master.enm_subject',
                'enquiry_master.enm_name',
                'enquiry_master.enm_is_myself',
                'enquiry_master.enm_created_at',
            ])
            ->map(fn ($row) => [
                'enquiry_id'   => (int) $row->enm_id,
                'subject'      => $row->enm_subject ?? '',
                'name'         => $row->enm_name ?? '',
                'is_reference' => ((int) $row->enm_is_myself) === 0,
                'created_at'   => $row->enm_created_at,
            ])
            ->all();
    }

    private function recentInteractions(int $companyId): array
    {
        return DB::table('business_analytics_events as bae')
            ->leftJoin('user_master as um', 'um.um_id', '=', 'bae.bae_um_id')
            ->leftJoin('user_details as ud', 'ud.ud_um_id', '=', 'bae.bae_um_id')
            ->where('bae.bae_cmp_id', $companyId)
            ->whereIn('bae.bae_event_type', self::EVENT_TYPES)
            ->orderByDesc('bae.bae_created_at')->limit(15)
            ->get([
                'bae.bae_event_type', 'bae.bae_source', 'bae.bae_created_at',
                'um.um_email_id', 'um.um_mobile_no', 'ud.ud_first_name', 'ud.ud_last_name',
            ])->map(fn ($row) => [
                'event_type' => $row->bae_event_type,
                'source' => $row->bae_source ?: 'UNKNOWN',
                'created_at' => $row->bae_created_at,
                'user_name' => trim(($row->ud_first_name ?? '').' '.($row->ud_last_name ?? '')) ?: ($row->um_email_id ? 'Registered user' : 'Public visitor'),
                'user_email' => $row->um_email_id,
                'user_mobile' => $row->um_mobile_no,
            ])->all();
    }

    private function ownsBusiness(int $userId, int $companyId): bool
    {
        if ($companyId <= 0) {
            return false;
        }
        return DB::table('user_companies_map')
            ->where('ucm_um_id', $userId)
            ->where('ucm_cmp_id', $companyId)
            ->exists();
    }

    /* Same token check the other API controllers use */
    private function tokenAuth($appAccessToken)
    {
        if (empty($appAccessToken)) {
            return ['status' => false, 'data' => 'Token Not Found In Request !!!'];
        }

        $userdata = $this->matchToken($appAccessToken);
        if (!$userdata['status']) {
            return ['status' => false, 'data' => 'Token Not Found !!!'];
        }

        $checkToken = UserDevice::where('user_id', '=', $userdata['data']->id)
            ->where('app_access_token', '=', $appAccessToken)
            ->first();
        if (empty($checkToken) || !$userdata['data']->exp || $userdata['data']->exp <= time()) {
            return ['status' => false, 'data' => 'Token Has Expired !!!'];
        }

        return ['status' => true, 'data' => [true, $userdata['data']->id]];
    }

    private static function matchToken($token)
    {
        try {
            $key = "1234567890qwertyuiopmnbvcxzasdfghjkl";
            $decoded = JWT::decode($token, $key, ['HS256']);
        } catch (\Exception $e) {
            return ['status' => false, 'data' => ''];
        }
        return ['status' => true, 'data' => $decoded];
    }
}
