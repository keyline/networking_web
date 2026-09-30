<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\BusinessPortfolio;
use App\Models\BusinessPortfolioItem;
use App\Models\BusinessPortfolioMedia;
use App\Models\Companies\CompaniesMaster;
use App\Models\Enquiries\EnquiryMaster;
use App\Services\PortfolioImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessPortfolioController extends Controller
{
    public function edit(Request $request, CompaniesMaster $company): View
    {
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

        return view('Member.BusinessPortfolio.edit', compact('company', 'portfolio', 'leads'));
    }

    public function update(Request $request, CompaniesMaster $company, PortfolioImageService $images): RedirectResponse
    {
        $this->authorizeOwner($request, $company);
        $data = $request->validate([
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
        if ($request->hasFile('hero_image')) {
            $newPath = $images->store($request->file('hero_image'), $company->cmp_id, 'hero');
            $images->delete($portfolio->hero_image);
            $data['hero_image'] = $newPath;
        }
        $data['whatsapp_enabled'] = $request->boolean('whatsapp_enabled');
        $data['contact_form_enabled'] = $request->boolean('contact_form_enabled');
        $portfolio->update($data);

        return back()->with('success', 'Changes saved as draft.');
    }

    public function storeItem(Request $request, CompaniesMaster $company, PortfolioImageService $images): RedirectResponse
    {
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

        return back()->with('success', 'Offering added. Publish when you are ready.');
    }

    public function destroyItem(Request $request, CompaniesMaster $company, BusinessPortfolioItem $item, PortfolioImageService $images): RedirectResponse
    {
        $this->authorizeOwner($request, $company);
        abort_unless((int) $item->company_id === (int) $company->cmp_id, 404);
        $images->delete($item->image);
        $item->delete();
        return back()->with('success', 'Offering removed.');
    }

    public function storeImage(Request $request, CompaniesMaster $company, PortfolioImageService $images): RedirectResponse
    {
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
        return back()->with('success', 'Photo compressed and added.');
    }

    public function storeVideo(Request $request, CompaniesMaster $company): RedirectResponse
    {
        $this->authorizeOwner($request, $company);
        abort_if(BusinessPortfolioMedia::where('company_id', $company->cmp_id)->where('type', 'youtube')->count() >= 10, 422, 'A maximum of 10 videos is allowed.');
        $data = $request->validate(['youtube_url' => ['required', 'url', 'max:255'], 'title' => ['nullable', 'string', 'max:150']]);
        $youtubeId = $this->youtubeId($data['youtube_url']);
        if (!$youtubeId) {
            return back()->withErrors(['youtube_url' => 'Enter a valid YouTube link.'])->withInput();
        }
        BusinessPortfolioMedia::create([
            'company_id' => $company->cmp_id, 'type' => 'youtube', 'youtube_id' => $youtubeId,
            'title' => $data['title'] ?? null,
            'sort_order' => (int) BusinessPortfolioMedia::where('company_id', $company->cmp_id)->max('sort_order') + 1,
        ]);
        return back()->with('success', 'YouTube video added.');
    }

    public function destroyMedia(Request $request, CompaniesMaster $company, BusinessPortfolioMedia $medium, PortfolioImageService $images): RedirectResponse
    {
        $this->authorizeOwner($request, $company);
        abort_unless((int) $medium->company_id === (int) $company->cmp_id, 404);
        $images->delete($medium->path);
        $medium->delete();
        return back()->with('success', 'Media removed.');
    }

    public function publish(Request $request, CompaniesMaster $company): RedirectResponse
    {
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
