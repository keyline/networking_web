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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class DashboardController extends Controller
{
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
        $recentInteractions = collect();
        if ($ownedBusinessIds->isNotEmpty()) {
            $recentInteractions = DB::table('business_analytics_events as bae')
                ->join('companies_details as cd', 'cd.cmpd_cmp_id', '=', 'bae.bae_cmp_id')
                ->leftJoin('user_master as um', 'um.um_id', '=', 'bae.bae_um_id')
                ->leftJoin('user_details as ud', 'ud.ud_um_id', '=', 'bae.bae_um_id')
                ->whereIn('bae.bae_cmp_id', $ownedBusinessIds)
                ->whereIn('bae.bae_event_type', ['view', 'call', 'whatsapp', 'email', 'share'])
                ->orderByDesc('bae.bae_created_at')->limit(15)
                ->get([
                    'bae.bae_event_type', 'bae.bae_created_at', 'bae.bae_source',
                    'cd.cmpd_name', 'um.um_email_id', 'um.um_mobile_no',
                    'ud.ud_first_name', 'ud.ud_last_name',
                ]);
        }

        $search = trim((string) $request->query('search'));
        $directory = collect();

        if ($search !== '') {
            $directory = CompaniesMaster::query()
                ->with(['details', 'users.userDetail'])
                ->where(function ($query) use ($search) {
                    $query->whereHas('details', function ($details) use ($search) {
                        $details->where('cmpd_name', 'like', "%{$search}%")
                            ->orWhere('cmpd_description', 'like', "%{$search}%")
                            ->orWhere('cmpd_email', 'like', "%{$search}%");
                    })->orWhereHas('users', function ($users) use ($search) {
                        $users->where('um_email_id', 'like', "%{$search}%")
                            ->orWhereHas('userDetail', function ($details) use ($search) {
                                $details->where('ud_first_name', 'like', "%{$search}%")
                                    ->orWhere('ud_last_name', 'like', "%{$search}%");
                            });
                    });
                })
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

        $recentBusinesses = CompaniesMaster::query()
            ->with('details')
            ->whereHas('details', fn ($query) => $query->where('cmpd_status', 1))
            ->whereDoesntHave('users', fn ($query) => $query->where('user_master.um_id', $member->um_id))
            ->orderByDesc('cmp_id')->limit(4)->get();

        $recentBusinesses->each(fn (CompaniesMaster $business) => $business->details?->ensurePublicSlug());

        $businessOptions = CompaniesMaster::query()
            ->with('details:cmpd_id,cmpd_cmp_id,cmpd_name')
            ->whereHas('details', fn ($query) => $query->where('cmpd_status', 1))
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
            ->limit(5)
            ->get();

        return view('Member.Dashboard.index', compact(
            'member', 'adminAccess', 'canManageBusinesses', 'recentInteractions', 'search', 'directory', 'myEnquiries', 'communityEnquiries', 'recentBusinesses', 'businessOptions', 'reviewBusinessOptions', 'memberOptions', 'meetingMemberOptions', 'recentMeetings'
        ));
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
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:250'],
            'description' => ['required', 'string', 'max:2000'],
            'visibility' => ['required', 'in:public,private'],
            'company_id' => ['nullable', 'integer', 'exists:companies_master,cmp_id'],
        ]);

        if ($data['visibility'] === 'private' && empty($data['company_id'])) {
            return back()->withErrors(['company_id' => 'Choose a business for a private enquiry.'])->withInput();
        }

        /** @var UserMaster $member */
        $member = Auth::guard('member')->user();
        $detail = $member->userDetail;

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

    public function storeReferral(Request $request): RedirectResponse
    {
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
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_dialog', 'referralModal');
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
