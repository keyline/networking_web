<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $range = in_array((int) $request->query('range', 30), [7, 30, 90, 365], true)
            ? (int) $request->query('range', 30)
            : 30;
        $from = Carbon::today()->subDays($range - 1);
        $search = trim((string) $request->query('search'));

        $eventBase = DB::table('business_analytics_events')->where('bae_created_at', '>=', $from);
        $eventTotals = (clone $eventBase)->selectRaw("
            COUNT(*) as total_events,
            SUM(bae_event_type = 'view') as views,
            SUM(bae_event_type = 'call') as calls,
            SUM(bae_event_type = 'whatsapp') as whatsapp,
            SUM(bae_event_type = 'email') as emails,
            SUM(bae_event_type = 'share') as shares
        ")->first();

        $enquiries = DB::table('enquiry_to_user')
            ->join('enquiry_master', 'enquiry_master.enm_id', '=', 'enquiry_to_user.etu_enm_id')
            ->where('enquiry_master.enm_created_at', '>=', $from)
            ->count();

        $views = (int) ($eventTotals->views ?? 0);
        $clicks = (int) ($eventTotals->calls ?? 0)
            + (int) ($eventTotals->whatsapp ?? 0)
            + (int) ($eventTotals->emails ?? 0)
            + (int) ($eventTotals->shares ?? 0);

        $data['stats'] = [
            'views' => $views,
            'unique_visitors' => (clone $eventBase)->where('bae_event_type', 'view')->distinct()
                ->count(DB::raw("CONCAT(COALESCE(bae_um_id, 0), '-', COALESCE(bae_device_id, ''))")),
            'clicks' => $clicks,
            'calls' => (int) ($eventTotals->calls ?? 0),
            'whatsapp' => (int) ($eventTotals->whatsapp ?? 0),
            'emails' => (int) ($eventTotals->emails ?? 0),
            'shares' => (int) ($eventTotals->shares ?? 0),
            'enquiries' => $enquiries,
            'conversion_rate' => $views > 0 ? round(($clicks + $enquiries) / $views * 100, 1) : 0,
        ];

        $eventAggregate = DB::table('business_analytics_events')
            ->where('bae_created_at', '>=', $from)
            ->groupBy('bae_cmp_id')
            ->selectRaw("bae_cmp_id,
                SUM(bae_event_type = 'view') as views,
                SUM(bae_event_type = 'call') as calls,
                SUM(bae_event_type = 'whatsapp') as whatsapp,
                SUM(bae_event_type = 'email') as emails,
                SUM(bae_event_type = 'share') as shares");

        $enquiryAggregate = DB::table('enquiry_to_user')
            ->join('enquiry_master', 'enquiry_master.enm_id', '=', 'enquiry_to_user.etu_enm_id')
            ->where('enquiry_master.enm_created_at', '>=', $from)
            ->groupBy('enquiry_to_user.etu_cmp_id')
            ->selectRaw('enquiry_to_user.etu_cmp_id, COUNT(*) as enquiries');

        $rankings = DB::table('companies_details as cd')
            ->leftJoinSub($eventAggregate, 'events', fn ($join) => $join->on('events.bae_cmp_id', '=', 'cd.cmpd_cmp_id'))
            ->leftJoinSub($enquiryAggregate, 'leads', fn ($join) => $join->on('leads.etu_cmp_id', '=', 'cd.cmpd_cmp_id'))
            ->selectRaw("cd.cmpd_cmp_id, cd.cmpd_name, cd.cmpd_logo,
                COALESCE(events.views, 0) as views, COALESCE(events.calls, 0) as calls,
                COALESCE(events.whatsapp, 0) as whatsapp, COALESCE(events.emails, 0) as emails,
                COALESCE(events.shares, 0) as shares, COALESCE(leads.enquiries, 0) as enquiries,
                (COALESCE(events.views, 0) + (COALESCE(events.calls, 0) + COALESCE(events.whatsapp, 0) + COALESCE(events.emails, 0) + COALESCE(events.shares, 0)) * 3 + COALESCE(leads.enquiries, 0) * 5) as traction_score")
            ->when($search !== '', fn ($query) => $query->where('cd.cmpd_name', 'like', "%{$search}%"))
            ->orderByDesc('traction_score')
            ->orderBy('cd.cmpd_name')
            ->paginate(20)
            ->withQueryString();

        $data['rankings'] = $rankings;
        $data['range'] = $range;
        $data['search'] = $search;
        $data['trend'] = (clone $eventBase)
            ->selectRaw("DATE(bae_created_at) as day, SUM(bae_event_type = 'view') as views,
                SUM(bae_event_type IN ('call','whatsapp','email','share')) as clicks")
            ->groupBy('day')->orderBy('day')->get();
        $data['sources'] = (clone $eventBase)->selectRaw("COALESCE(NULLIF(bae_source, ''), 'UNKNOWN') as source, COUNT(*) as total")
            ->groupBy('source')->orderByDesc('total')->get();

        return $this->admin_after_login_layout('Business Analytics', 'analytics.index', $data);
    }
}
