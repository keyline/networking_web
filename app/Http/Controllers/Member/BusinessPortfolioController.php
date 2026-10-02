<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\BusinessPortfolio;
use App\Models\BusinessPortfolioItem;
use App\Models\BusinessPortfolioMedia;
use App\Models\Companies\CompaniesMaster;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\Enquiries\EnquiryMaster;
use App\Services\PortfolioImageService;
use App\Services\PortfolioRouteToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessPortfolioController extends Controller
{
    public function edit(Request $request, string $companyToken, PortfolioRouteToken $tokens): View
    {
        $company = $this->companyFromToken($companyToken, $tokens);
        $this->authorizeOwner($request, $company);
        $company->load(['details', 'categories', 'portfolio.items', 'portfolio.media']);
        $portfolio = $company->portfolio ?: BusinessPortfolio::create([
            'company_id' => $company->cmp_id,
            'about' => $company->details?->cmpd_description,
            'whatsapp_number' => $company->details?->cmpd_whatsapp_no,
            'notification_email' => $company->details?->cmpd_email,
            'notification_mobile' => $company->details?->cmpd_phone,
        ]);
        $portfolio->load(['items', 'media']);
        $leadIds = DB::table('enquiry_to_user')->where('etu_cmp_id', $company->cmp_id)->pluck('etu_enm_id');
        $leads = EnquiryMaster::whereIn('enm_id', $leadIds)->latest('enm_created_at')->limit(10)->get();

        $categories = BusinessCategoryMaster::where('status', 1)->orderBy('name')->get();
        return view('Member.BusinessPortfolio.edit', compact('company', 'portfolio', 'leads', 'categories', 'companyToken'));
    }

    public function update(Request $request, string $companyToken, PortfolioImageService $images, PortfolioRouteToken $tokens): RedirectResponse
    {
        $company = $this->companyFromToken($companyToken, $tokens);
        $this->authorizeOwner($request, $company);
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'category_ids' => ['required', 'array', 'min:1', 'max:3'],
            'category_ids.*' => ['integer', 'distinct', 'exists:business_category_master,bcm_id'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:15'],
            'business_whatsapp' => ['nullable', 'string', 'max:15'],
            'company_registration' => ['nullable', 'string', 'max:50'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'established_year' => ['nullable', 'digits:4', 'integer', 'min:1800', 'max:'.now()->year],
            'address1' => ['nullable', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'address3' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'about' => ['nullable', 'string', 'max:4000'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'notification_email' => ['nullable', 'email', 'max:255'],
            'notification_mobile' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'whatsapp_message' => ['nullable', 'string', 'max:255'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $portfolio = BusinessPortfolio::firstOrCreate(['company_id' => $company->cmp_id]);
        $details = $company->details;
        abort_unless($details, 404);
        $detailData = [
            'cmpd_name' => $data['business_name'],
            'cmpd_email' => $data['business_email'] ?? null,
            'cmpd_phone' => $data['business_phone'] ?? null,
            'cmpd_whatsapp_no' => $data['business_whatsapp'] ?? null,
            'cmpd_company_regn_no' => $data['company_registration'] ?? null,
            'cmpd_gst_no' => $data['gst_number'] ?? null,
            'cmpd_pan_no' => $data['pan_number'] ?? null,
            'cmpd_estd_year' => $data['established_year'] ?? null,
            'cmpd_address1' => $data['address1'] ?? null,
            'cmpd_address2' => $data['address2'] ?? null,
            'cmpd_address3' => $data['address3'] ?? null,
            'cmpd_pincode' => $data['pincode'] ?? null,
            'cmpd_description' => $data['about'] ?? $details->cmpd_description,
        ];
        if ($request->hasFile('logo')) {
            File::ensureDirectoryExists(public_path('uploads/company'));
            $logo = $request->file('logo');
            $logoName = Str::uuid().'.'.$logo->extension();
            $logo->move(public_path('uploads/company'), $logoName);
            $detailData['cmpd_logo'] = $logoName;
        }
        if ($request->hasFile('hero_image')) {
            $newPath = $images->store($request->file('hero_image'), $company->cmp_id, 'hero');
            $images->delete($portfolio->hero_image);
            $data['hero_image'] = $newPath;
        }
        $data['whatsapp_enabled'] = $request->boolean('whatsapp_enabled');
        $data['contact_form_enabled'] = $request->boolean('contact_form_enabled');
        DB::transaction(function () use ($details, $detailData, $company, $data, $portfolio) {
            $details->update($detailData);
            $company->categories()->sync($data['category_ids']);
            $portfolio->update(collect($data)->only(['tagline', 'about', 'website', 'notification_email', 'notification_mobile', 'whatsapp_number', 'whatsapp_message', 'hero_image'])->merge([
                'whatsapp_enabled' => request()->boolean('whatsapp_enabled'),
                'contact_form_enabled' => request()->boolean('contact_form_enabled'),
            ])->all());
        });

        return $this->redirectToTab($companyToken, 'profile')->with('success', 'Changes saved as draft.');
    }

    public function storeItem(Request $request, string $companyToken, PortfolioImageService $images, PortfolioRouteToken $tokens): RedirectResponse
    {
        $company = $this->companyFromToken($companyToken, $tokens);
        $this->authorizeOwner($request, $company);
        abort_if(BusinessPortfolioItem::where('company_id', $company->cmp_id)->count() >= 30, 422, 'A maximum of 30 products and services is allowed.');
        $data = $request->validate([
            'type' => ['required', Rule::in(['product', 'service'])],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1500'],
            'price_label' => ['nullable', 'string', 'max:100'],
            'external_url' => ['nullable', 'url:http,https', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);
        if ($request->hasFile('image')) {
            $data['image'] = $images->store($request->file('image'), $company->cmp_id, 'offerings');
        }
        $data['company_id'] = $company->cmp_id;
        $data['is_active'] = true;
        $data['sort_order'] = (int) BusinessPortfolioItem::where('company_id', $company->cmp_id)->max('sort_order') + 1;
        BusinessPortfolioItem::create($data);

        return $this->redirectToTab($companyToken, 'offerings')->with('success', 'Offering added. Publish when you are ready.');
    }

    public function destroyItem(Request $request, string $companyToken, BusinessPortfolioItem $item, PortfolioImageService $images, PortfolioRouteToken $tokens): RedirectResponse
    {
        $company = $this->companyFromToken($companyToken, $tokens);
        $this->authorizeOwner($request, $company);
        abort_unless((int) $item->company_id === (int) $company->cmp_id, 404);
        $images->delete($item->image);
        $item->delete();
        return $this->redirectToTab($companyToken, 'offerings')->with('success', 'Offering removed.');
    }

    public function storeImage(Request $request, string $companyToken, PortfolioImageService $images, PortfolioRouteToken $tokens): RedirectResponse
    {
        $company = $this->companyFromToken($companyToken, $tokens);
        $this->authorizeOwner($request, $company);
        abort_if(BusinessPortfolioMedia::where('company_id', $company->cmp_id)->where('type', 'image')->count() >= 24, 422, 'A maximum of 24 gallery images is allowed.');
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'title' => ['required', 'string', 'max:150'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);
        BusinessPortfolioMedia::create([
            'company_id' => $company->cmp_id, 'type' => 'image',
            'path' => $images->store($request->file('image'), $company->cmp_id),
            'title' => $data['title'],
            'caption' => $data['caption'] ?? null,
            'sort_order' => (int) BusinessPortfolioMedia::where('company_id', $company->cmp_id)->max('sort_order') + 1,
        ]);
        return $this->redirectToTab($companyToken, 'gallery')->with('success', 'Photo compressed and added.');
    }

    public function storeVideo(Request $request, string $companyToken, PortfolioRouteToken $tokens): RedirectResponse
    {
        $company = $this->companyFromToken($companyToken, $tokens);
        $this->authorizeOwner($request, $company);
        abort_if(BusinessPortfolioMedia::where('company_id', $company->cmp_id)->where('type', 'youtube')->count() >= 10, 422, 'A maximum of 10 videos is allowed.');
        $data = $request->validate(['youtube_url' => ['required', 'url', 'max:255'], 'title' => ['nullable', 'string', 'max:150']]);
        $youtubeId = $this->youtubeId($data['youtube_url']);
        if (!$youtubeId) {
            return $this->redirectToTab($companyToken, 'videos')->withErrors(['youtube_url' => 'Enter a valid YouTube link.'])->withInput();
        }
        BusinessPortfolioMedia::create([
            'company_id' => $company->cmp_id, 'type' => 'youtube', 'youtube_id' => $youtubeId,
            'title' => $data['title'] ?? null,
            'sort_order' => (int) BusinessPortfolioMedia::where('company_id', $company->cmp_id)->max('sort_order') + 1,
        ]);
        return $this->redirectToTab($companyToken, 'videos')->with('success', 'YouTube video added.');
    }

    public function destroyMedia(Request $request, string $companyToken, BusinessPortfolioMedia $medium, PortfolioImageService $images, PortfolioRouteToken $tokens): RedirectResponse
    {
        $company = $this->companyFromToken($companyToken, $tokens);
        $this->authorizeOwner($request, $company);
        abort_unless((int) $medium->company_id === (int) $company->cmp_id, 404);
        $tab = $medium->type === 'image' ? 'gallery' : 'videos';
        $images->delete($medium->path);
        $medium->delete();
        return $this->redirectToTab($companyToken, $tab)->with('success', 'Media removed.');
    }

    public function publish(Request $request, string $companyToken, PortfolioRouteToken $tokens): RedirectResponse
    {
        $company = $this->companyFromToken($companyToken, $tokens);
        $this->authorizeOwner($request, $company);
        $portfolio = BusinessPortfolio::with(['items', 'media'])->firstOrCreate(['company_id' => $company->cmp_id]);
        $snapshot = $portfolio->only(['tagline', 'about', 'hero_image', 'website', 'whatsapp_number', 'whatsapp_message', 'whatsapp_enabled', 'contact_form_enabled']);
        $snapshot['items'] = $portfolio->items->where('is_active', true)->values()->toArray();
        $snapshot['media'] = $portfolio->media->where('is_active', true)->values()->toArray();
        $portfolio->update(['is_published' => true, 'published_at' => now(), 'published_snapshot' => $snapshot]);
        $company->details?->ensurePublicSlug();
        return back()->with('success', 'Your business page is live.');
    }

    private function authorizeOwner(Request $request, CompaniesMaster $company): void
    {
        abort_unless($request->user('member')->companies()->where('companies_master.cmp_id', $company->cmp_id)->exists(), 403);
    }

    private function redirectToTab(string $companyToken, string $tab): RedirectResponse
    {
        return redirect()->to(route('member.portfolio.edit', $companyToken).'#'.$tab);
    }

    private function companyFromToken(string $token, PortfolioRouteToken $tokens): CompaniesMaster
    {
        return CompaniesMaster::findOrFail($tokens->decode($token));
    }

    private function youtubeId(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (!in_array(preg_replace('/^www\./', '', $host), ['youtube.com', 'm.youtube.com', 'youtu.be'], true)) return null;
        if (str_contains($host, 'youtu.be')) $id = trim((string) parse_url($url, PHP_URL_PATH), '/');
        elseif (preg_match('~/(?:embed|shorts)/([A-Za-z0-9_-]{11})~', $url, $m)) $id = $m[1];
        else { parse_str((string) parse_url($url, PHP_URL_QUERY), $query); $id = $query['v'] ?? ''; }
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
    }
}
