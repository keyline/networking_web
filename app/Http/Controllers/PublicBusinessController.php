<?php

namespace App\Http\Controllers;

use App\Models\Companies\CompaniesDetail;
use App\Models\Enquiries\EnquiryMaster;
use App\Models\BusinessPortfolio;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\GeneralSetting;
use App\Models\Page;
use App\Models\User\UserMaster;
use App\Notifications\BusinessLeadEmailNotification;
use App\Notifications\BusinessLeadSmsNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PublicBusinessController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->integer('category');

        $businesses = CompaniesDetail::query()
            ->with(['companies.categories'])
            ->where('cmpd_status', 1)
            ->whereNotNull('public_slug')
            ->whereHas('companies.users', fn ($query) => $query->where('um_status', 2))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($details) use ($search) {
                    $details->where('cmpd_name', 'like', "%{$search}%")
                        ->orWhere('cmpd_description', 'like', "%{$search}%")
                        ->orWhereHas('companies.categories', fn ($categories) => $categories->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($categoryId, fn ($query) => $query->whereHas(
                'companies.categories',
                fn ($categories) => $categories->where('business_category_master.bcm_id', $categoryId)
            ))
            ->orderBy('cmpd_name')
            ->paginate(12)
            ->withQueryString();

        return view('front.members', array_merge($this->siteData(), [
            'title' => 'Members · Net-Works',
            'businesses' => $businesses,
            'categories' => BusinessCategoryMaster::where('status', 1)->orderBy('name')->get(),
            'search' => $search,
            'categoryId' => $categoryId,
        ]));
    }

    public function show(string $slug): View
    {
        $business = CompaniesDetail::query()
            ->with(['companies.categories', 'companies.users.userDetail', 'gallery'])
            ->where('public_slug', $slug)
            ->firstOrFail();
        $isActive = (int) $business->cmpd_status === 1;

        $social = DB::table('company_sociallink')->where('cs_cmp_id', $business->cmpd_cmp_id)->first();
        $portfolioModel = BusinessPortfolio::where('company_id', $business->cmpd_cmp_id)->where('is_published', true)->first();
        $portfolio = $portfolioModel?->published_snapshot;

        $activeReviews = $business->reviews()->where('status', 1);
        $reviewCount = (clone $activeReviews)->count();
        $reviewAverage = $reviewCount
            ? round((float) (clone $activeReviews)->avg('rev_rating'), 1)
            : 0.0;
        $reviews = $activeReviews
            ->with('user:ud_um_id,ud_first_name,ud_last_name')
            ->whereNotNull('rev_comment')
            ->where('rev_comment', '!=', '')
            ->latest('rev_id')
            ->limit(4)
            ->get();

        return view('front.business-profile', compact(
            'business',
            'social',
            'portfolio',
            'reviews',
            'reviewCount',
            'reviewAverage',
            'isActive'
        ));
    }

    private function siteData(): array
    {
        $navigation = fn (string $location) => Page::with([
            'children' => fn ($query) => $query->where('status', 1)->whereIn('nav_location', [$location, 'both']),
        ])->whereNull('parent_id')->where('status', 1)->whereIn('nav_location', [$location, 'both'])
            ->orderBy('nav_order')->orderBy('page_name')->get();

        return [
            'generalSetting' => GeneralSetting::find(1),
            'headerNavigation' => $navigation('header'),
            'footerNavigation' => $navigation('footer'),
        ];
    }

    public function lead(Request $request, string $slug): RedirectResponse
    {
        $business = CompaniesDetail::query()
            ->where('public_slug', $slug)
            ->where('cmpd_status', 1)
            ->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email:rfc', 'max:250', 'required_without:phone'],
            'phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/', 'required_without:email'],
            'whatsapp' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'subject' => ['nullable', 'string', 'max:250', 'required_without:product_title'],
            'product_title' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:3000'],
            'website' => ['nullable', 'max:0'],
        ], [
            'email.required_without' => 'Provide an email address or mobile number.',
            'phone.required_without' => 'Provide a mobile number or email address.',
            'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'whatsapp.regex' => 'Enter a valid 10-digit WhatsApp number.',
        ]);

        $ownerId = DB::table('user_companies_map')
            ->where('ucm_cmp_id', $business->cmpd_cmp_id)
            ->value('ucm_um_id');

        if (!$ownerId) {
            return back()->withInput()->withErrors(['lead' => 'This business cannot receive enquiries yet. Please use its listed contact details.']);
        }

        $lead = DB::transaction(function () use ($data, $business, $ownerId) {
            $lead = EnquiryMaster::create([
                'enm_name' => $data['name'],
                'enm_email' => $data['email'] ?? null,
                'enm_phone' => $data['phone'] ?? null,
                'enm_whatsapp' => $data['whatsapp'] ?? null,
                'enm_question_for' => $data['product_title'] ?? $business->cmpd_name,
                'enm_subject' => !empty($data['product_title']) ? 'Enquiry about '.$data['product_title'] : $data['subject'],
                'enm_description' => $data['message'],
                'enm_type' => 1,
                'enm_is_myself' => 0,
                'enm_status' => 1,
            ]);

            DB::table('enquiry_to_user')->insert([
                'etu_cmp_id' => $business->cmpd_cmp_id,
                'etu_enm_id' => $lead->enm_id,
                'etu_um_id' => $ownerId,
                'etu_created_at' => now(),
            ]);
            return $lead;
        });

        $owner = UserMaster::find($ownerId);
        if ($owner) {
            $portfolio = BusinessPortfolio::where('company_id', $business->cmpd_cmp_id)->first();
            try {
                $email = $portfolio?->notification_email ?: $owner->um_email_id;
                if ($email) {
                    \Illuminate\Support\Facades\Notification::route('mail', $email)
                        ->notify(new BusinessLeadEmailNotification($lead, $business->cmpd_name));
                }
            } catch (\Throwable $exception) {
                Log::warning('Business lead email alert failed', ['lead_id' => $lead->enm_id, 'error' => $exception->getMessage()]);
            }
            try {
                $mobile = $portfolio?->notification_mobile ?: $owner->um_mobile_no;
                if ($mobile) $owner->notify(new BusinessLeadSmsNotification($lead, $business->cmpd_name, $mobile));
            } catch (\Throwable $exception) {
                Log::warning('Business lead SMS alert failed', ['lead_id' => $lead->enm_id, 'error' => $exception->getMessage()]);
            }
        }

        return redirect()->route('business.show', $business->public_slug)->with('lead_success', true);
    }
}
