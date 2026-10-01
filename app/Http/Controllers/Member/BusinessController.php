<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function create(): View
    {
        return view('Member.Business.create', [
            'categories' => BusinessCategoryMaster::where('status', 1)->orderBy('name')->get(),
            'countries' => Country::where('status', 1)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'category_ids' => ['required', 'array', 'min:1', 'max:5'],
            'category_ids.*' => ['integer', 'distinct', 'exists:business_category_master,bcm_id'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:15'],
            'description' => ['nullable', 'string', 'max:3000'],
            'address1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'integer', 'exists:countries,id'],
            'state' => ['nullable', 'integer', Rule::exists('states', 'id')->where(fn ($query) => $query->where('country_id', $request->input('country')))],
            'pincode' => ['nullable', 'string', 'max:10'],
        ]);

        $member = $request->user('member');
        $company = DB::transaction(function () use ($data, $member) {
            $company = CompaniesMaster::create([]);
            CompaniesDetail::create([
                'cmpd_cmp_id' => $company->cmp_id,
                'cmpd_name' => $data['business_name'],
                'cmpd_description' => $data['description'] ?? 'Business profile pending approval.',
                'cmpd_email' => $data['business_email'] ?? $member->um_email_id,
                'cmpd_phone' => $data['business_phone'] ?? $member->um_mobile_no,
                'cmpd_address1' => $data['address1'] ?? null,
                'cmpd_address3' => $data['city'] ?? null,
                'cmpd_country' => $data['country'] ?? null,
                'cmpd_state' => $data['state'] ?? null,
                'cmpd_pincode' => $data['pincode'] ?? null,
                'cmpd_status' => 0,
                'cmpd_is_document_valid' => '0',
            ]);
            $company->users()->attach($member->um_id);
            $company->categories()->attach($data['category_ids']);
            return $company;
        });

        return redirect()->route('member.portfolio.edit', $company->portfolioRouteToken())
            ->with('success', 'Business created and submitted for approval. You can complete its profile now.');
    }
}
