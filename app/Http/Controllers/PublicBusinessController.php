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
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicBusinessController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->integer('category');
        $engagementFrom = Carbon::today()->subDays(29);

        // Keep the directory badge aligned with the default 30-day Admin Analytics report.
        $eventEngagement = DB::table('business_analytics_events')
            ->where('bae_created_at', '>=', $engagementFrom)
            ->groupBy('bae_cmp_id')
            ->selectRaw("bae_cmp_id,
                SUM(bae_event_type = 'view') as engagement_views,
                SUM(bae_event_type IN ('call','whatsapp','email','share')) as engagement_contacts");

        $enquiryEngagement = DB::table('enquiry_to_user')
            ->join('enquiry_master', 'enquiry_master.enm_id', '=', 'enquiry_to_user.etu_enm_id')
            ->where('enquiry_master.enm_created_at', '>=', $engagementFrom)
            ->groupBy('enquiry_to_user.etu_cmp_id')
            ->selectRaw('enquiry_to_user.etu_cmp_id, COUNT(*) as engagement_enquiries');

        $businesses = CompaniesDetail::query()
            ->select('companies_details.*')
            ->leftJoinSub($eventEngagement, 'directory_events', function ($join) {
                $join->on('directory_events.bae_cmp_id', '=', 'companies_details.cmpd_cmp_id');
            })
            ->leftJoinSub($enquiryEngagement, 'directory_enquiries', function ($join) {
                $join->on('directory_enquiries.etu_cmp_id', '=', 'companies_details.cmpd_cmp_id');
            })
            ->selectRaw("CASE
                WHEN COALESCE(directory_events.engagement_views, 0) > 0
                THEN ROUND(
                    (COALESCE(directory_events.engagement_contacts, 0) + COALESCE(directory_enquiries.engagement_enquiries, 0))
                    / directory_events.engagement_views * 100,
                    1
                )
                ELSE 0
            END AS engagement_rate")
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
            ->orderBy('companies_details.cmpd_name')
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

    public function show(Request $request, string $slug): View
    {
        $business = CompaniesDetail::query()
            ->with(['companies.categories', 'companies.users.userDetail', 'gallery'])
            ->where('public_slug', $slug)
            ->firstOrFail();
        $isActive = (int) $business->cmpd_status === 1;
        $this->logInteraction($request, (int) $business->cmpd_cmp_id, 'view');

        $social = DB::table('company_sociallink')->where('cs_cmp_id', $business->cmpd_cmp_id)->first();
        $portfolioModel = BusinessPortfolio::where('company_id', $business->cmpd_cmp_id)->where('is_published', true)->first();
        $portfolio = $portfolioModel?->published_snapshot;
        $publishedAbout = trim((string) ($portfolio['about'] ?? $business->cmpd_description));
        $aboutText = CompaniesDetail::isSetupDescription($publishedAbout) ? null : $publishedAbout;

        $signedInMember = Auth::guard('member')->user();
        $isBusinessOwner = $signedInMember
            && $business->companies->users->contains(
                fn (UserMaster $owner) => (int) $owner->um_id === (int) $signedInMember->um_id
            );
        $aboutEditUrl = $isBusinessOwner
            ? route('member.portfolio.edit', $business->companies->portfolioRouteToken()).'?focus=about#profile'
            : null;

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
            'isActive',
            'aboutText',
            'isBusinessOwner',
            'aboutEditUrl'
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

        abort_unless(Auth::guard('member')->check(), 403, 'Register or sign in to contact this business.');

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

    public function contact(Request $request, string $slug, string $type): RedirectResponse
    {
        abort_unless(in_array($type, ['call', 'whatsapp', 'email'], true), 404);
        $business = CompaniesDetail::where('public_slug', $slug)->where('cmpd_status', 1)->firstOrFail();
        $this->logInteraction($request, (int) $business->cmpd_cmp_id, $type);

        if ($type === 'call' && $business->cmpd_phone) {
            return redirect()->away('tel:'.preg_replace('/[^0-9+]/', '', $business->cmpd_phone));
        }
        if ($type === 'email' && $business->cmpd_email) {
            return redirect()->away('mailto:'.$business->cmpd_email);
        }
        $number = preg_replace('/\D/', '', (string) ($business->cmpd_whatsapp_no ?: $business->cmpd_phone));
        if ($type === 'whatsapp' && $number) {
            $message = Str::limit((string) $request->query('message', 'Hello, I found your Net-Works business page.'), 500, '');
            return redirect()->away('https://wa.me/'.$number.'?text='.rawurlencode($message));
        }

        return redirect()->route('business.show', $slug)->withErrors(['contact' => 'This contact method is not available.']);
    }

    public function track(Request $request, string $slug): JsonResponse
    {
        $data = $request->validate(['event_type' => ['required', 'in:share']]);
        $business = CompaniesDetail::where('public_slug', $slug)->where('cmpd_status', 1)->firstOrFail();
        $this->logInteraction($request, (int) $business->cmpd_cmp_id, $data['event_type']);
        return response()->json(['tracked' => true]);
    }

    private function logInteraction(Request $request, int $companyId, string $type): void
    {
        $userId = Auth::guard('member')->id();
        if ($userId && DB::table('user_companies_map')->where('ucm_um_id', $userId)->where('ucm_cmp_id', $companyId)->exists()) {
            return;
        }

        $deviceId = hash('sha256', $request->session()->getId());
        $duplicate = DB::table('business_analytics_events')
            ->where('bae_cmp_id', $companyId)->where('bae_event_type', $type)
            ->where('bae_um_id', $userId)->where('bae_device_id', $deviceId)
            ->where('bae_created_at', '>=', now()->subMinutes(30))->exists();
        if (!$duplicate) {
            DB::table('business_analytics_events')->insert([
                'bae_cmp_id' => $companyId,
                'bae_event_type' => $type,
                'bae_um_id' => $userId,
                'bae_device_id' => $deviceId,
                'bae_source' => 'WEB',
                'bae_created_at' => now(),
            ]);
        }
    }
}
