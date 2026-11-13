<?php

namespace App\Http\Controllers\Member\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin as AdminAccount;
use App\Models\Companies\CompaniesMaster;
use App\Models\Enquiries\EnquiryMaster;
use App\Models\MemberMeeting;
use App\Models\Review\ReviewMaster;
use App\Models\UserActivity;
use App\Models\User\UserMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function members(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $memberMatches = $search !== '' ? app(\App\Services\BusinessSearchIndex::class)->memberIds($search) : [];
        $members = UserMaster::activeMembers()
            ->with([
                'userDetail:ud_id,ud_um_id,ud_first_name,ud_last_name',
                'companies.details:cmpd_id,cmpd_cmp_id,cmpd_name,cmpd_status,public_slug',
                'companies.categories:bcm_id,name',
            ])
            // Smart search (business name, owner, category, services, phone) via the search index.
            ->when($search !== '', function ($query) use ($memberMatches) {
                $query->whereIn('um_id', $memberMatches ?: [0]);
                if ($memberMatches) {
                    $query->orderByRaw('FIELD(um_id, '.implode(',', $memberMatches).')');
                }
            })
            ->orderByDesc('um_id')
            ->paginate(20)
            ->withQueryString();

        $memberIds = $members->getCollection()->pluck('um_id');
        $since = now()->subDays(30);
        $meetingCounts = collect();
        foreach (['reported_by_um_id', 'counterpart_um_id'] as $memberColumn) {
            DB::table('member_meetings')
                ->whereIn($memberColumn, $memberIds)
                ->selectRaw("{$memberColumn} as member_id, COUNT(*) as all_time, SUM(CASE WHEN meeting_at >= ? THEN 1 ELSE 0 END) as recent", [$since])
                ->groupBy($memberColumn)
                ->get()
                ->each(function ($row) use ($meetingCounts) {
                    $counts = $meetingCounts[$row->member_id] ?? ['recent' => 0, 'all_time' => 0];
                    $counts['recent'] += (int) $row->recent;
                    $counts['all_time'] += (int) $row->all_time;
                    $meetingCounts[$row->member_id] = $counts;
                });
        }
        $referenceCounts = DB::table('enquiry_to_user as etu')
            ->join('enquiry_master as em', 'em.enm_id', '=', 'etu.etu_enm_id')
            ->whereIn('etu.etu_um_id', $memberIds)
            ->where('em.enm_is_myself', 0)
            ->selectRaw('etu.etu_um_id as member_id, COUNT(*) as all_time, SUM(CASE WHEN em.enm_created_at >= ? THEN 1 ELSE 0 END) as recent', [$since])
            ->groupBy('etu.etu_um_id')
            ->get()->keyBy('member_id');
        $activityCounts = DB::table('business_analytics_events')
            ->whereIn('bae_um_id', $memberIds)
            ->selectRaw('bae_um_id as member_id, COUNT(*) as all_time, SUM(CASE WHEN bae_created_at >= ? THEN 1 ELSE 0 END) as recent', [$since])
            ->groupBy('bae_um_id')
            ->get()->keyBy('member_id');
        $reviewCounts = DB::table('reviews')
            ->whereIn('rev_um_id', $memberIds)
            ->selectRaw('rev_um_id as member_id, COUNT(*) as all_time, SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as recent', [$since])
            ->groupBy('rev_um_id')
            ->get()->keyBy('member_id');

        $members->getCollection()->each(function (UserMaster $member) use ($meetingCounts, $referenceCounts, $activityCounts, $reviewCounts) {
            $meetings = $meetingCounts[$member->um_id] ?? ['recent' => 0, 'all_time' => 0];
            $references = $referenceCounts[$member->um_id] ?? null;
            $activity = $activityCounts[$member->um_id] ?? null;
            $reviews = $reviewCounts[$member->um_id] ?? null;
            $member->setAttribute('activity_stats', [
                'recent' => [
                    'meetings' => (int) $meetings['recent'], 'references' => (int) ($references->recent ?? 0),
                    'activity' => (int) ($activity->recent ?? 0), 'reviews' => (int) ($reviews->recent ?? 0),
                ],
                'all_time' => [
                    'meetings' => (int) $meetings['all_time'], 'references' => (int) ($references->all_time ?? 0),
                    'activity' => (int) ($activity->all_time ?? 0), 'reviews' => (int) ($reviews->all_time ?? 0),
                ],
            ]);
        });

        return view('Member.Members.index', compact('members', 'search'));
    }

    public function index(Request $request): View
    {
        /** @var UserMaster $member */
        $member = Auth::guard('member')->user();
        $member->load(['userDetail', 'companies.details']);
        $canManageBusinesses = $member->hasActiveBusinessMembership();
        $adminAccess = AdminAccount::query()
            ->where('user_master_id', $member->um_id)
            ->where('status', 1)
            ->first();
        $ownedBusinessIds = $member->companies->pluck('cmp_id');
        $businessAnalytics = null;
        $analyticsPeriod = 'last_30';
        $analyticsPeriods = [];
        if ($ownedBusinessIds->isNotEmpty()) {
            // Reporting period chosen in the dashboard dropdown (?period=...).
            $periods = [
                'this_month' => ['This month', now()->startOfMonth(), now()],
                'last_month' => ['Last month', now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
                'last_30' => ['Last 30 days', now()->subDays(30), now()],
                'last_90' => ['Last 3 months', now()->subMonthsNoOverflow(3), now()],
                'all' => ['All time', null, now()],
            ];
            $analyticsPeriod = array_key_exists($request->query('period'), $periods) ? $request->query('period') : 'last_30';
            [, $analyticsFrom, $analyticsTo] = $periods[$analyticsPeriod];
            $inPeriod = function ($query, string $column) use ($analyticsFrom, $analyticsTo) {
                return $query->when($analyticsFrom, fn ($q) => $q->where($column, '>=', $analyticsFrom))
                    ->where($column, '<=', $analyticsTo);
            };
            $analyticsPeriods = collect($periods)->map(fn ($period) => $period[0])->all();
            $eventTotals = DB::table('business_analytics_events')
                ->whereIn('bae_cmp_id', $ownedBusinessIds)
                ->tap(fn ($q) => $inPeriod($q, 'bae_created_at'))
                ->selectRaw("SUM(CASE WHEN bae_event_type = 'view' THEN 1 ELSE 0 END) as page_views")
                ->selectRaw("SUM(CASE WHEN bae_event_type IN ('call', 'whatsapp', 'email', 'share') THEN 1 ELSE 0 END) as contact_clicks")
                ->first();
            $uniqueVisitors = DB::table('business_analytics_events')
                ->whereIn('bae_cmp_id', $ownedBusinessIds)
                ->where('bae_event_type', 'view')
                ->tap(fn ($q) => $inPeriod($q, 'bae_created_at'))
                ->select(['bae_um_id', 'bae_device_id'])
                ->get()
                ->map(fn ($event) => $event->bae_um_id ? 'member:'.$event->bae_um_id : 'device:'.($event->bae_device_id ?: 'anonymous'))
                ->unique()
                ->count();
            $enquiries = DB::table('enquiry_to_user as etu')
                ->join('enquiry_master as em', 'em.enm_id', '=', 'etu.etu_enm_id')
                ->whereIn('etu.etu_cmp_id', $ownedBusinessIds)
                ->tap(fn ($q) => $inPeriod($q, 'em.enm_created_at'))
                ->count();
            // References given is a lifetime member total, independent of the page analytics period.
            $referencesGiven = DB::table('enquiry_to_user as etu')
                ->join('enquiry_master as em', 'em.enm_id', '=', 'etu.etu_enm_id')
                ->where('etu.etu_um_id', $member->um_id)
                ->where('em.enm_is_myself', 0)
                ->distinct()->count('etu.etu_enm_id');
            // Meetings done is a lifetime member total, independent of the page analytics period.
            $meetingsDone = DB::table('member_meetings')
                ->where(fn ($query) => $query->where('reported_by_um_id', $member->um_id)->orWhere('counterpart_um_id', $member->um_id))
                ->where('meeting_at', '<=', now())
                ->count();
            $businessAnalytics = [
                'page_views' => (int) ($eventTotals->page_views ?? 0),
                'unique_visitors' => $uniqueVisitors,
                'contact_clicks' => (int) ($eventTotals->contact_clicks ?? 0),
                'enquiries' => $enquiries,
                'references_given' => $referencesGiven,
                'meetings_done' => $meetingsDone,
            ];

        }

        $search = trim((string) $request->query('search'));
        $directory = collect();

        if ($search !== '') {
            $directoryIds = app(\App\Services\BusinessSearchIndex::class)->businessIds($search, true, 8);
            $directory = CompaniesMaster::query()
                ->with(['details', 'users.userDetail'])
                ->whereIn('cmp_id', $directoryIds ?: [0])
                ->when($directoryIds, fn ($query) => $query->orderByRaw('FIELD(cmp_id, '.implode(',', $directoryIds).')'))
                ->limit(8)
                ->get();

            $directory->each(fn (CompaniesMaster $business) => $business->details?->ensurePublicSlug());
        }

        $myEnquiryIds = DB::table('enquiry_to_user')
            ->where('etu_um_id', $member->um_id)
            ->pluck('etu_enm_id');

        $myEnquiries = EnquiryMaster::query()
            ->with([
                'companies.details:cmpd_id,cmpd_cmp_id,cmpd_name',
                'companies.categories:bcm_id,name',
                'companies.users' => fn ($query) => $query
                    ->where('um_status', 2)
                    ->with('userDetail:ud_id,ud_um_id,ud_first_name,ud_last_name'),
            ])
            ->whereIn('enm_id', $myEnquiryIds)
            ->latest('enm_created_at')->limit(5)->get()
            ->each(function (EnquiryMaster $enquiry) {
                if ((int) $enquiry->enm_type === 2) {
                    $enquiry->recipient_label = 'All members';
                    return;
                }

                $business = $enquiry->companies->first();
                if (!$business) {
                    $enquiry->recipient_label = 'Selected business';
                    return;
                }

                $recipient = $business->users->first();
                $memberName = trim(($recipient?->userDetail?->ud_first_name ?? '').' '.($recipient?->userDetail?->ud_last_name ?? ''))
                    ?: ($recipient?->um_user_name ?: 'Member');
                $businessName = $business->details?->cmpd_name ?: 'Business #'.$business->cmp_id;
                $categoryName = $business->categories->first()?->name;

                $enquiry->recipient_label = $memberName.' - '.$businessName.($categoryName ? " ({$categoryName})" : '');
            });

        $communityEnquiries = EnquiryMaster::query()
            ->where('enm_type', 2)->where('enm_status', 1)
            ->latest('enm_created_at')->limit(5)->get();
        $communityEnquiryLabels = DB::table('enquiry_to_user as etu')
            ->join('user_master as sender', 'sender.um_id', '=', 'etu.etu_um_id')
            ->whereIn('etu.etu_enm_id', $communityEnquiries->pluck('enm_id'))
            ->select('etu.etu_enm_id', 'sender.um_id', 'sender.um_utm_id')
            ->get()->mapWithKeys(fn ($sender) => [
                (int) $sender->etu_enm_id => (int) $sender->um_id === (int) $member->um_id
                    ? 'myself'
                    : ((int) $sender->um_utm_id === 1 ? 'Visitor' : null),
            ]);

        $businessOptions = CompaniesMaster::query()
            ->with('details:cmpd_id,cmpd_cmp_id,cmpd_name')
            ->whereHas('details', fn ($query) => $query->where('cmpd_status', 1))
            // a member cannot send a direct enquiry to their own business
            ->whereDoesntHave('users', fn ($query) => $query->where('user_master.um_id', $member->um_id))
            ->orderBy('cmp_id')->get();

        $reviewedBusinessIds = ReviewMaster::query()
            ->where('rev_um_id', $member->um_id)
            ->pluck('rev_cmp_id');
        $reviewBusinessOptions = CompaniesMaster::query()
            ->with('details:cmpd_id,cmpd_cmp_id,cmpd_name,cmpd_status')
            ->whereHas('details', fn ($query) => $query->where('cmpd_status', 1))
            ->whereDoesntHave('users', fn ($query) => $query->where('user_master.um_id', $member->um_id))
            ->when($reviewedBusinessIds->isNotEmpty(), fn ($query) => $query->whereNotIn('cmp_id', $reviewedBusinessIds))
            ->get()
            ->sortBy(fn (CompaniesMaster $business) => $business->details?->cmpd_name ?? '', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $memberOptions = UserMaster::query()
            ->with([
                'userDetail:ud_id,ud_um_id,ud_first_name,ud_last_name',
                'companies' => fn ($query) => $query
                    ->with(['details:cmpd_id,cmpd_cmp_id,cmpd_name,cmpd_status', 'categories:bcm_id,name'])
                    ->whereHas('details', fn ($details) => $details->where('cmpd_status', 1)),
            ])
            ->where('um_status', 2)
            ->where('um_id', '!=', $member->um_id)
            ->whereHas('companies.details', fn ($query) => $query->where('cmpd_status', 1))
            ->get()
            ->flatMap(function (UserMaster $recipient) {
                $memberName = trim(($recipient->userDetail?->ud_first_name ?? '').' '.($recipient->userDetail?->ud_last_name ?? ''))
                    ?: ($recipient->um_user_name ?: 'Member');

                return $recipient->companies->map(function (CompaniesMaster $business) use ($recipient, $memberName) {
                    $businessName = $business->details?->cmpd_name ?: 'Business #'.$business->cmp_id;
                    $categoryNames = $business->categories->pluck('name')->filter()->implode(', ') ?: 'Unclassified';

                    return (object) [
                        'member_id' => $recipient->um_id,
                        'company_id' => $business->cmp_id,
                        'label' => "{$memberName} - {$businessName} ({$categoryNames})",
                    ];
                });
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $meetingMemberOptions = UserMaster::query()
            ->with([
                'userDetail:ud_id,ud_um_id,ud_first_name,ud_last_name',
                'companies' => fn ($query) => $query
                    ->with('details:cmpd_id,cmpd_cmp_id,cmpd_name,cmpd_status')
                    ->whereHas('details', fn ($details) => $details->where('cmpd_status', 1)),
            ])
            ->where('um_status', 2)
            ->where('um_id', '!=', $member->um_id)
            ->whereHas('companies.details', fn ($query) => $query->where('cmpd_status', 1))
            ->get()
            ->map(function (UserMaster $recipient) {
                $memberName = trim(($recipient->userDetail?->ud_first_name ?? '').' '.($recipient->userDetail?->ud_last_name ?? ''))
                    ?: ($recipient->um_user_name ?: 'Member');
                $businessNames = $recipient->companies->pluck('details.cmpd_name')->filter()->implode(', ');

                return (object) [
                    'member_id' => $recipient->um_id,
                    'name' => $memberName,
                    'label' => $memberName.($businessNames ? ' — '.$businessNames : ''),
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $recentMeetings = MemberMeeting::query()
            ->with([
                'reporter.userDetail:ud_id,ud_um_id,ud_first_name,ud_last_name',
                'counterpart.userDetail:ud_id,ud_um_id,ud_first_name,ud_last_name',
                'inviter.userDetail:ud_id,ud_um_id,ud_first_name,ud_last_name',
            ])
            ->where(fn ($query) => $query->where('reported_by_um_id', $member->um_id)
                ->orWhere('counterpart_um_id', $member->um_id))
            ->latest('meeting_at')
            ->get();

        // Lifetime totals shown as big numbers on the quick-action cards (4 small indexed counts).
        $quickCounts = [
            'references' => DB::table('enquiry_to_user as etu')->join('enquiry_master as em', 'em.enm_id', '=', 'etu.etu_enm_id')
                ->where('etu.etu_um_id', $member->um_id)->where('em.enm_is_myself', 0)->distinct()->count('etu.etu_enm_id'),
            'enquiries' => DB::table('enquiry_to_user as etu')->join('enquiry_master as em', 'em.enm_id', '=', 'etu.etu_enm_id')
                ->where('etu.etu_um_id', $member->um_id)->where('em.enm_is_myself', 1)->distinct()->count('etu.etu_enm_id'),
            'reviews' => DB::table('reviews')->where('rev_um_id', $member->um_id)->count(),
            'meetings' => DB::table('member_meetings')
                ->where(fn ($query) => $query->where('reported_by_um_id', $member->um_id)->orWhere('counterpart_um_id', $member->um_id))
                ->where('meeting_at', '<=', now())->count(),
        ];

        return view('Member.Dashboard.index', compact(
            'communityEnquiryLabels',
            'quickCounts',
            'member', 'adminAccess', 'canManageBusinesses', 'businessAnalytics', 'analyticsPeriod', 'analyticsPeriods', 'search', 'directory', 'myEnquiries', 'communityEnquiries', 'businessOptions', 'reviewBusinessOptions', 'memberOptions', 'meetingMemberOptions', 'recentMeetings'
        ));
    }

    /** "My enquiries" / "My reviews" lists for the dashboard eye buttons, 10 at a time (JSON). */
    public function myList(Request $request, string $type)
    {
        abort_unless(in_array($type, ['enquiries', 'reviews'], true), 404);
        $member = $request->user('member');

        if ($type === 'enquiries') {
            $items = DB::table('enquiry_to_user as etu')
                ->join('enquiry_master as em', 'em.enm_id', '=', 'etu.etu_enm_id')
                ->where('etu.etu_um_id', $member->um_id)->where('em.enm_is_myself', 1)
                ->orderByDesc('em.enm_created_at')->orderByDesc('em.enm_id')
                ->simplePaginate(10, ['em.enm_id', 'em.enm_subject', 'em.enm_description', 'em.enm_status', 'em.enm_created_at as created_at', 'etu.etu_cmp_id']);
            $names = DB::table('companies_details')->whereIn('cmpd_cmp_id', $items->getCollection()->pluck('etu_cmp_id')->filter()->unique())->pluck('cmpd_name', 'cmpd_cmp_id');
            $items->setCollection($items->getCollection()->map(function ($row) use ($names) {
                $row->to_label = (int) $row->etu_cmp_id === 0 ? 'all members' : ($names[$row->etu_cmp_id] ?? 'a business');
                return $row;
            }));
        } else {
            $items = DB::table('reviews as r')
                ->leftJoin('companies_details as cd', 'cd.cmpd_cmp_id', '=', 'r.rev_cmp_id')
                ->where('r.rev_um_id', $member->um_id)
                ->orderByDesc('r.created_at')->orderByDesc('r.rev_id')
                ->simplePaginate(10, ['r.rev_id', 'r.rev_rating', 'r.rev_comment', 'r.created_at', 'cd.cmpd_name as business_name']);
        }

        return response()->json([
            'html' => view('Member.Dashboard.partials.my-list', compact('items', 'type'))->render(),
            'next' => $items->hasMorePages() ? $items->nextPageUrl() : null,
            'empty' => $items->isEmpty() && $items->onFirstPage(),
        ]);
    }

    /** References this member has given, 10 at a time, for the dashboard pop-up (JSON). */
    public function referencesGiven(Request $request)
    {
        $member = $request->user('member');
        $references = DB::table('enquiry_to_user as etu')
            ->join('enquiry_master as em', 'em.enm_id', '=', 'etu.etu_enm_id')
            ->where('etu.etu_um_id', $member->um_id)
            ->where('em.enm_is_myself', 0)
            ->orderByDesc('em.enm_created_at')->orderByDesc('em.enm_id')
            ->simplePaginate(10, ['em.enm_id', 'em.enm_subject', 'em.enm_name', 'em.enm_phone', 'em.enm_email', 'em.enm_description', 'em.enm_created_at', 'etu.etu_cmp_id']);

        // Recipient labels for just this batch: "Owner - Business (Category)".
        $rows = $references->getCollection();
        $companyIds = $rows->pluck('etu_cmp_id')->filter()->unique()->values();
        $names = DB::table('companies_details')->whereIn('cmpd_cmp_id', $companyIds)->pluck('cmpd_name', 'cmpd_cmp_id');
        $owners = DB::table('user_companies_map as ucm')->leftJoin('user_details as ud', 'ud.ud_um_id', '=', 'ucm.ucm_um_id')
            ->whereIn('ucm.ucm_cmp_id', $companyIds)->get(['ucm.ucm_cmp_id', 'ud.ud_first_name', 'ud.ud_last_name'])->keyBy('ucm_cmp_id');
        $categories = DB::table('categories_to_companies as ctc')->join('business_category_master as bcm', 'bcm.bcm_id', '=', 'ctc.ctc_bcm_id')
            ->whereIn('ctc.ctc_cmp_id', $companyIds)->get(['ctc.ctc_cmp_id', 'bcm.name'])->groupBy('ctc_cmp_id');
        $references->setCollection($rows->map(function ($row) use ($names, $owners, $categories) {
            if ((int) $row->etu_cmp_id === 0) {
                $row->recipient_label = 'All members';
                return $row;
            }
            $owner = $owners[$row->etu_cmp_id] ?? null;
            $ownerName = trim(($owner->ud_first_name ?? '').' '.($owner->ud_last_name ?? ''));
            $category = optional($categories->get($row->etu_cmp_id))->first()?->name;
            $row->recipient_label = trim(($ownerName ? $ownerName.' - ' : '').($names[$row->etu_cmp_id] ?? 'Business #'.$row->etu_cmp_id).($category ? " ({$category})" : ''));
            $row->recipient_business = $names[$row->etu_cmp_id] ?? 'Business #'.$row->etu_cmp_id;
            $row->recipient_owner = $ownerName;
            $row->recipient_category = $category;
            return $row;
        }));

        return response()->json([
            'html' => view('Member.Dashboard.partials.references-given', compact('references'))->render(),
            'next' => $references->hasMorePages() ? $references->nextPageUrl() : null,
            'empty' => $references->isEmpty() && $references->onFirstPage(),
        ]);
    }

    public function businessActivity(Request $request)
    {
        $member = $request->user('member');
        // The owner's businesses (id => name, logo); the page shows one business at a time.
        $ownedBusinesses = DB::table('user_companies_map as ucm')
            ->join('companies_details as cd', 'cd.cmpd_cmp_id', '=', 'ucm.ucm_cmp_id')
            ->where('ucm.ucm_um_id', $member->um_id)
            ->orderBy('cd.cmpd_name')
            ->get(['cd.cmpd_cmp_id as id', 'cd.cmpd_name as name', 'cd.cmpd_logo as logo']);
        $selectedBusiness = $ownedBusinesses->firstWhere('id', (int) $request->query('business')) ?? $ownedBusinesses->first();
        $businessIds = collect($selectedBusiness ? [$selectedBusiness->id] : []);
        $since = now()->subDays(30)->startOfDay();
        $types = ['view', 'call', 'whatsapp', 'email', 'share'];

        // Feed: last 30 days only, served by the (bae_cmp_id, bae_event_type, bae_created_at) index;
        // simplePaginate avoids a COUNT(*) over the whole history. 20 rows per lazy-loaded batch.
        $interactions = DB::table('business_analytics_events')
            ->whereIn('bae_cmp_id', $businessIds)
            ->whereIn('bae_event_type', $types)
            ->where('bae_created_at', '>=', $since)
            ->orderByDesc('bae_created_at')
            ->orderByDesc('bae_id')
            ->simplePaginate(20, ['bae_id', 'bae_cmp_id', 'bae_um_id', 'bae_event_type', 'bae_created_at', 'bae_source'])
            ->appends(['business' => $selectedBusiness?->id]);

        // Names/contacts only for the rows in this batch.
        $rows = $interactions->getCollection();
        $userIds = $rows->pluck('bae_um_id')->filter()->unique()->values();
        $businessNames = DB::table('companies_details')->whereIn('cmpd_cmp_id', $rows->pluck('bae_cmp_id')->unique())->pluck('cmpd_name', 'cmpd_cmp_id');
        $users = $userIds->isEmpty() ? collect() : DB::table('user_master')->whereIn('um_id', $userIds)->get(['um_id', 'um_email_id', 'um_mobile_no'])->keyBy('um_id');
        $details = $userIds->isEmpty() ? collect() : DB::table('user_details')->whereIn('ud_um_id', $userIds)->get(['ud_um_id', 'ud_first_name', 'ud_last_name', 'ud_whatsapp_no'])->keyBy('ud_um_id');
        $interactions->setCollection($rows->map(function ($row) use ($businessNames, $users, $details) {
            $user = $users[$row->bae_um_id] ?? null;
            $detail = $details[$row->bae_um_id] ?? null;
            $row->cmpd_name = $businessNames[$row->bae_cmp_id] ?? 'Your business';
            $row->um_email_id = $user->um_email_id ?? null;
            $row->um_mobile_no = $user->um_mobile_no ?? null;
            $row->ud_first_name = $detail->ud_first_name ?? null;
            $row->ud_last_name = $detail->ud_last_name ?? null;
            // wa.me link: WhatsApp number from the profile, else the mobile; 10-digit numbers get +91.
            $wa = preg_replace('/\D+/', '', (string) (($detail->ud_whatsapp_no ?? null) ?: ($user->um_mobile_no ?? '')));
            $row->whatsapp_url = strlen($wa) >= 10 ? 'https://wa.me/'.(strlen($wa) === 10 ? '91'.$wa : $wa) : null;

            return $row;
        }));

        $lastDay = (string) $request->query('last_day', '');

        // Lazy-load request from the page: return just the next batch of items.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('Member.BusinessActivity.partials.items', compact('interactions', 'lastDay'))->render(),
                'next' => $interactions->hasMorePages() ? $interactions->nextPageUrl() : null,
            ]);
        }

        // Chart summary: one grouped query (at most 30 days x 5 types rows), cached for 5 minutes.
        $summary = Cache::remember('member-activity-summary:'.$member->um_id.':'.md5($businessIds->implode(',')), 300, function () use ($businessIds, $since, $types) {
            $grouped = DB::table('business_analytics_events')
                ->whereIn('bae_cmp_id', $businessIds)
                ->whereIn('bae_event_type', $types)
                ->where('bae_created_at', '>=', $since)
                ->selectRaw('DATE(bae_created_at) as day, bae_event_type as type, COUNT(*) as total')
                ->groupBy('day', 'type')
                ->get();
            $days = collect(range(29, 0))->mapWithKeys(fn ($ago) => [now()->subDays($ago)->toDateString() => 0])->all();
            foreach ($grouped as $row) {
                if (isset($days[$row->day])) {
                    $days[$row->day] += (int) $row->total;
                }
            }
            $byType = collect($types)->mapWithKeys(fn ($type) => [$type => (int) $grouped->where('type', $type)->sum('total')])->all();
            $members = DB::table('business_analytics_events')
                ->whereIn('bae_cmp_id', $businessIds)
                ->whereIn('bae_event_type', $types)
                ->where('bae_created_at', '>=', $since)
                ->whereNotNull('bae_um_id')
                ->distinct()->count('bae_um_id');

            return ['days' => $days, 'byType' => $byType, 'total' => array_sum($byType), 'members' => $members];
        });

        return view('Member.BusinessActivity.index', compact('interactions', 'summary', 'lastDay', 'ownedBusinesses', 'selectedBusiness'));
    }

    public function openAdminDashboard(Request $request): RedirectResponse
    {
        /** @var UserMaster $member */
        $member = Auth::guard('member')->user();
        abort_unless((int) $member->um_status === 2 && $member->companies()->exists(), 403);

        $admin = AdminAccount::query()
            ->where('user_master_id', $member->um_id)
            ->where('status', 1)
            ->firstOrFail();

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();
        $request->session()->put([
            'user_id' => $admin->id,
            'name' => $admin->name,
            'type' => $admin->type,
            'email' => $admin->email,
            'company_id' => $admin->company_id,
            'is_admin_login' => 1,
            'admin_login_source' => 'member_dashboard',
        ]);
        $admin->update(['last_login_at' => now()]);

        UserActivity::insert([
            'user_email' => $admin->email,
            'user_name' => $admin->name,
            'user_type' => 'ADMIN',
            'ip_address' => $request->ip(),
            'activity_type' => 1,
            'activity_details' => 'Login Success via Member Dashboard',
            'platform_type' => 'WEB',
        ]);

        return redirect('/admin/dashboard');
    }

    public function storeEnquiry(Request $request): RedirectResponse
    {
        if ($request->filled('requirement')) {
            $requirement = trim((string) $request->input('requirement'));
            $request->merge([
                'subject' => Str::limit(preg_replace('/\s+/', ' ', $requirement), 250, ''),
                'description' => $requirement,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:250'],
            'description' => ['required', 'string', 'max:2000'],
            'visibility' => ['required', 'in:public,private'],
            'company_id' => ['nullable', 'integer', 'exists:companies_master,cmp_id'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_dialog', 'requirementModal')->with('requirement_tab', 'own');
        }

        $data = $validator->validated();

        if ($data['visibility'] === 'private' && empty($data['company_id'])) {
            return back()->withErrors(['company_id' => 'Choose a business for a private enquiry.'])->withInput()->with('open_dialog', 'requirementModal')->with('requirement_tab', 'own');
        }

        /** @var UserMaster $member */
        $member = Auth::guard('member')->user();
        $detail = $member->userDetail;

        if ($data['visibility'] === 'private' && $this->ownsBusiness($member, (int) $data['company_id'])) {
            return back()->withErrors(['company_id' => 'You cannot send an enquiry to your own business.'])->withInput()->with('open_dialog', 'requirementModal')->with('requirement_tab', 'own');
        }

        DB::transaction(function () use ($data, $member, $detail) {
            $enquiry = EnquiryMaster::create([
                'enm_name' => trim(($detail?->ud_first_name ?? '') . ' ' . ($detail?->ud_last_name ?? '')) ?: $member->um_user_name,
                'enm_email' => $member->um_email_id,
                'enm_phone' => $member->um_mobile_no,
                'enm_whatsapp' => $detail?->ud_whatsapp_no,
                'enm_address' => $detail?->ud_addr_1,
                'enm_subject' => $data['subject'],
                'enm_description' => $data['description'],
                'enm_type' => $data['visibility'] === 'public' ? 2 : 1,
                'enm_is_myself' => 1,
                'enm_status' => 1,
            ]);

            DB::table('enquiry_to_user')->insert([
                'etu_cmp_id' => $data['visibility'] === 'private' ? $data['company_id'] : 0,
                'etu_enm_id' => $enquiry->enm_id,
                'etu_um_id' => $member->um_id,
                'etu_created_at' => now(),
            ]);
        });

        return back()->with('success', 'Your enquiry has been shared successfully.');
    }

    public function storeMeeting(Request $request): RedirectResponse
    {
        /** @var UserMaster $member */
        $member = Auth::guard('member')->user();
        abort_unless((int) $member->um_status === 2 && $member->companies()
            ->whereHas('details', fn ($query) => $query->where('cmpd_status', 1))->exists(), 403);

        $validator = Validator::make($request->all(), [
            'counterpart_member_id' => ['required', 'integer'],
            'invited_by' => ['required', 'in:me,other'],
            'meeting_at' => ['required', 'date'],
            'mode' => ['required', 'in:in_person,online,phone'],
            'location' => ['nullable', 'required_if:mode,in_person', 'string', 'max:250'],
            'details' => ['required', 'string', 'max:2000'],
            'outcome' => ['nullable', 'string', 'max:2000'],
            'follow_up_on' => ['nullable', 'date'],
        ]);

        $validator->after(function ($validator) use ($request, $member) {
            $counterpartId = (int) $request->input('counterpart_member_id');
            $eligible = $counterpartId !== (int) $member->um_id && UserMaster::query()
                ->where('um_id', $counterpartId)
                ->where('um_status', 2)
                ->whereHas('companies.details', fn ($query) => $query->where('cmpd_status', 1))
                ->exists();
            if (!$eligible) {
                $validator->errors()->add('counterpart_member_id', 'Choose another active registered member.');
            }
            if ($request->filled('meeting_at') && $request->filled('follow_up_on')) {
                try {
                    if (\Carbon\Carbon::parse($request->input('follow_up_on'))->startOfDay()
                        ->lt(\Carbon\Carbon::parse($request->input('meeting_at'))->startOfDay())) {
                        $validator->errors()->add('follow_up_on', 'The follow-up date must be on or after the meeting date.');
                    }
                } catch (\Throwable) {
                    // The standard date rules will report malformed values.
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_dialog', 'meetingModal');
        }

        $data = $validator->validated();
        $meetingAt = \Carbon\Carbon::parse($data['meeting_at'], 'Asia/Kolkata')->utc();
        $counterpartId = (int) $data['counterpart_member_id'];
        $duplicate = MemberMeeting::query()
            ->where('meeting_at', $meetingAt)
            ->where(function ($query) use ($member, $counterpartId) {
                $query->where(fn ($pair) => $pair->where('reported_by_um_id', $member->um_id)->where('counterpart_um_id', $counterpartId))
                    ->orWhere(fn ($pair) => $pair->where('reported_by_um_id', $counterpartId)->where('counterpart_um_id', $member->um_id));
            })->exists();
        if ($duplicate) {
            return back()->withErrors(['meeting_at' => 'This one-to-one meeting has already been registered.'])
                ->withInput()->with('open_dialog', 'meetingModal');
        }

        MemberMeeting::create([
            'reported_by_um_id' => $member->um_id,
            'counterpart_um_id' => $counterpartId,
            'invited_by_um_id' => $data['invited_by'] === 'me' ? $member->um_id : $counterpartId,
            'meeting_at' => $meetingAt,
            'mode' => $data['mode'],
            'location' => $data['location'] ?? null,
            'details' => $data['details'],
            'outcome' => $data['outcome'] ?? null,
            'follow_up_on' => $data['follow_up_on'] ?? null,
        ]);

        return back()->with('success', 'One-to-one member meeting registered successfully.');
    }

    public function storeReview(Request $request): RedirectResponse
    {
        /** @var UserMaster $member */
        $member = Auth::guard('member')->user();
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $validator->after(function ($validator) use ($request, $member) {
            $companyId = (int) $request->input('company_id');
            $eligible = CompaniesMaster::query()
                ->where('cmp_id', $companyId)
                ->whereHas('details', fn ($query) => $query->where('cmpd_status', 1))
                ->whereDoesntHave('users', fn ($query) => $query->where('user_master.um_id', $member->um_id))
                ->exists();
            if (!$eligible) {
                $validator->errors()->add('company_id', 'Choose an active business that you do not own.');
            }
            if (ReviewMaster::query()->where('rev_um_id', $member->um_id)->where('rev_cmp_id', $companyId)->exists()) {
                $validator->errors()->add('company_id', 'You have already reviewed this business.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_dialog', 'reviewModal');
        }

        $data = $validator->validated();
        ReviewMaster::create([
            'rev_cmp_id' => $data['company_id'],
            'rev_um_id' => $member->um_id,
            'rev_rating' => $data['rating'],
            'rev_comment' => $data['comment'],
            'status' => 1,
        ]);

        return back()->with('success', 'Your review has been published successfully.');
    }

    /** True when the member is linked to (owns) the given business. */
    private function ownsBusiness(?UserMaster $member, int $companyId): bool
    {
        return $member && $companyId > 0 && DB::table('user_companies_map')
            ->where('ucm_um_id', $member->um_id)->where('ucm_cmp_id', $companyId)->exists();
    }

    public function storeReferral(Request $request): RedirectResponse
    {
        if ($request->filled('requirement')) {
            $request->merge(['note' => trim((string) $request->input('requirement'))]);
        }

        $validator = Validator::make($request->all(), [
            'audience' => ['required', 'in:all_members,individual'],
            'referral_company_id' => ['nullable', 'integer', 'exists:companies_master,cmp_id'],
            'name' => ['required', 'string', 'max:250'],
            'email' => ['nullable', 'email', 'max:250'],
            'phone' => ['required', 'string', 'max:30'],
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->input('audience') === 'individual' && !$request->filled('referral_company_id')) {
                $validator->errors()->add('referral_company_id', 'Choose a business for an individual reference.');
            }
            if ($request->input('audience') === 'individual' && $request->filled('referral_company_id')
                && $this->ownsBusiness(Auth::guard('member')->user(), (int) $request->input('referral_company_id'))) {
                $validator->errors()->add('referral_company_id', 'You cannot share a reference with your own business.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_dialog', 'requirementModal')->with('requirement_tab', 'someone');
        }

        $data = $validator->validated();

        /** @var UserMaster $member */
        $member = Auth::guard('member')->user();

        DB::transaction(function () use ($data, $member) {
            $enquiry = EnquiryMaster::create([
                'enm_name' => $data['name'], 'enm_email' => $data['email'] ?? null, 'enm_phone' => $data['phone'],
                'enm_subject' => 'Member referral', 'enm_description' => $data['note'],
                'enm_type' => $data['audience'] === 'all_members' ? 2 : 1, 'enm_is_myself' => 0, 'enm_status' => 1,
            ]);

            DB::table('enquiry_to_user')->insert([
                'etu_cmp_id' => $data['audience'] === 'individual' ? $data['referral_company_id'] : 0, 'etu_enm_id' => $enquiry->enm_id,
                'etu_um_id' => $member->um_id, 'etu_created_at' => now(),
            ]);
        });

        return back()->with('success', 'Reference shared with the selected member.');
    }
}
