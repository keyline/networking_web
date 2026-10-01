<?php

namespace App\Http\Controllers\Member\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Companies\CompaniesMaster;
use App\Models\Enquiries\EnquiryMaster;
use App\Models\User\UserMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var UserMaster $member */
        $member = Auth::guard('member')->user();
        $member->load(['userDetail', 'companies.details']);
        $canManageBusinesses = $member->hasActiveBusinessMembership();

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
                    $categoryName = $business->categories->first()?->name ?: 'Unclassified';

                    return (object) [
                        'member_id' => $recipient->um_id,
                        'company_id' => $business->cmp_id,
                        'label' => "{$memberName} - {$businessName} ({$categoryName})",
                    ];
                });
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('Member.Dashboard.index', compact(
            'member', 'canManageBusinesses', 'search', 'directory', 'myEnquiries', 'communityEnquiries', 'recentBusinesses', 'businessOptions', 'memberOptions'
        ));
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

    public function storeReferral(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies_master,cmp_id'],
            'name' => ['required', 'string', 'max:250'],
            'email' => ['nullable', 'email', 'max:250'],
            'phone' => ['required', 'string', 'max:30'],
            'note' => ['required', 'string', 'max:2000'],
        ]);

        /** @var UserMaster $member */
        $member = Auth::guard('member')->user();

        DB::transaction(function () use ($data, $member) {
            $enquiry = EnquiryMaster::create([
                'enm_name' => $data['name'], 'enm_email' => $data['email'], 'enm_phone' => $data['phone'],
                'enm_subject' => 'Member referral', 'enm_description' => $data['note'],
                'enm_type' => 1, 'enm_is_myself' => 0, 'enm_status' => 1,
            ]);

            DB::table('enquiry_to_user')->insert([
                'etu_cmp_id' => $data['company_id'], 'etu_enm_id' => $enquiry->enm_id,
                'etu_um_id' => $member->um_id, 'etu_created_at' => now(),
            ]);
        });

        return back()->with('success', 'Reference shared with the selected member.');
    }
}
