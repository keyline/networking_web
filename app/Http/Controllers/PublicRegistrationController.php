<?php

namespace App\Http\Controllers;

use App\Models\Business\BusinessCategoryMaster;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\GeneralSetting;
use App\Models\Page;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicRegistrationController extends Controller
{
    public function create()
    {
        return view('front.join', $this->viewData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:user_master,um_email_id'],
            'mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_master,um_mobile_no'],
            'whatsapp' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_categories' => ['required', 'array', 'min:1'],
            'business_categories.*' => [
                'required', 'integer', 'distinct',
                Rule::exists('business_category_master', 'bcm_id')->where(fn ($query) => $query->where('status', 1)),
            ],
            'business_email' => ['nullable', 'email:rfc', 'max:255'],
            'business_phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'business_whatsapp' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'gst_number' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[A-Z0-9]$/'],
            'description' => ['nullable', 'string', 'max:3000'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'regex:/^[1-9][0-9]{5}$/'],
            'company_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'max:0'], // honeypot
        ], [
            'mobile.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'whatsapp.regex' => 'Enter a valid 10-digit WhatsApp number.',
            'business_phone.regex' => 'Enter a valid 10-digit business phone number.',
            'business_whatsapp.regex' => 'Enter a valid 10-digit business WhatsApp number.',
            'gst_number.regex' => 'Enter a valid GST number using uppercase letters.',
            'consent.accepted' => 'Please confirm that the submitted information is correct.',
        ]);

        $logoName = null;
        if ($request->hasFile('company_logo')) {
            $directory = public_path('uploads/company');
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
            $logoName = Str::uuid() . '.' . $request->file('company_logo')->extension();
            $request->file('company_logo')->move($directory, $logoName);
        }

        try {
            $registrationNumber = DB::transaction(function () use ($data, $logoName) {
                $user = UserMaster::create([
                    'um_utm_id' => 2,
                    'um_email_id' => strtolower($data['email']),
                    'um_mobile_no' => $data['mobile'],
                    'um_password' => Hash::make(Str::random(40)),
                    'um_status' => 1,
                    'um_profile_type' => 'O',
                ]);

                $registrationNumber = 'EN' . str_pad((string) $user->um_id, 6, '0', STR_PAD_LEFT);
                $user->update(['um_user_name' => $registrationNumber]);

                UserDetails::create([
                    'ud_um_id' => $user->um_id,
                    'ud_first_name' => $data['first_name'],
                    'ud_last_name' => $data['last_name'] ?? null,
                    'ud_whatsapp_no' => ($data['whatsapp'] ?? null) ?: $data['mobile'],
                    'ud_addr_1' => $data['address_line_1'],
                    'ud_addr_2' => trim(($data['address_line_2'] ?? '') . ($data['city'] ? ', ' . $data['city'] : '')),
                    'ud_business_name' => $data['business_name'],
                    'ud_business_addr_1' => $data['address_line_1'],
                    'ud_business_addr_2' => trim(($data['address_line_2'] ?? '') . ($data['city'] ? ', ' . $data['city'] : '')),
                    'ud_business_category' => (string) $data['business_categories'][0],
                    'ud_pincode' => $data['pincode'],
                ]);

                $company = CompaniesMaster::create([]);
                CompaniesDetail::create([
                    'cmpd_cmp_id' => $company->cmp_id,
                    'cmpd_name' => $data['business_name'],
                    'public_slug' => $this->uniqueBusinessSlug($data['business_name']),
                    'cmpd_description' => ($data['description'] ?? null) ?: 'Business profile submitted for approval.',
                    'cmpd_email' => ($data['business_email'] ?? null) ?: strtolower($data['email']),
                    'cmpd_phone' => ($data['business_phone'] ?? null) ?: $data['mobile'],
                    'cmpd_whatsapp_no' => ($data['business_whatsapp'] ?? null) ?: (($data['whatsapp'] ?? null) ?: $data['mobile']),
                    'cmpd_gst_no' => $data['gst_number'] ?? null,
                    'cmpd_logo' => $logoName,
                    'cmpd_address1' => $data['address_line_1'],
                    'cmpd_address2' => $data['address_line_2'] ?? null,
                    'cmpd_address3' => $data['city'],
                    'cmpd_pincode' => $data['pincode'],
                    'cmpd_status' => 0,
                    'cmpd_is_document_valid' => '0',
                ]);

                DB::table('user_companies_map')->insert([
                    'ucm_cmp_id' => $company->cmp_id,
                    'ucm_um_id' => $user->um_id,
                ]);
                DB::table('categories_to_companies')->insert(array_map(fn ($categoryId) => [
                    'ctc_bcm_id' => $categoryId,
                    'ctc_cmp_id' => $company->cmp_id,
                    'ctc_created_at' => now(),
                ], $data['business_categories']));

                return $registrationNumber;
            });
        } catch (\Throwable $exception) {
            if ($logoName && is_file(public_path('uploads/company/' . $logoName))) {
                @unlink(public_path('uploads/company/' . $logoName));
            }
            report($exception);
            return back()->withInput()->withErrors(['registration' => 'We could not save your registration. Please try again.']);
        }

        return redirect()->route('join.create')->with('registration_success', $registrationNumber);
    }

    private function viewData(): array
    {
        $generalSetting = GeneralSetting::find(1);

        return [
            'title' => 'Join Net-Works',
            'generalSetting' => $generalSetting,
            'categories' => BusinessCategoryMaster::where('status', 1)->orderBy('name')->get(),
            'headerNavigation' => $this->navigationFor('header'),
            'footerNavigation' => $this->navigationFor('footer'),
        ];
    }

    private function navigationFor(string $location)
    {
        return Page::with(['children' => fn ($query) => $query->where('status', 1)->whereIn('nav_location', [$location, 'both'])])
            ->whereNull('parent_id')
            ->where('status', 1)
            ->whereIn('nav_location', [$location, 'both'])
            ->orderBy('nav_order')
            ->orderBy('page_name')
            ->get();
    }

    private function uniqueBusinessSlug(string $name): string
    {
        return CompaniesDetail::uniquePublicSlug($name);
    }
}
