<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\GeneralSetting;
use App\Models\Page;
use App\Models\PublicRegistrationSetting;
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
        abort_unless(PublicRegistrationSetting::current()->enabled, 403, 'Public registration is currently closed.');

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:user_master,um_email_id'],
            'mobile' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_master,um_mobile_no'],
            'whatsapp' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'integer', 'exists:countries,id'],
            'state' => ['nullable', 'integer', Rule::exists('states', 'id')->where(fn ($query) => $query->where('country_id', $request->input('country')))],
            'pincode' => ['nullable', 'regex:/^[1-9][0-9]{5}$/'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ]);

        $registrationNumber = DB::transaction(function () use ($data) {
            $user = UserMaster::create([
                'um_utm_id' => 1,
                'um_email_id' => strtolower($data['email']),
                'um_mobile_no' => $data['mobile'],
                'um_password' => Hash::make(Str::random(40)),
                'um_status' => 1,
                'um_profile_type' => 'G',
            ]);
            $registrationNumber = 'EN'.str_pad((string) $user->um_id, 6, '0', STR_PAD_LEFT);
            $user->update(['um_user_name' => $registrationNumber]);

            UserDetails::create([
                'ud_um_id' => $user->um_id,
                'ud_first_name' => $data['first_name'],
                'ud_last_name' => $data['last_name'] ?? null,
                'ud_whatsapp_no' => ($data['whatsapp'] ?? null) ?: $data['mobile'],
                'ud_addr_1' => $data['address_line_1'] ?? null,
                'ud_addr_2' => trim(($data['address_line_2'] ?? '').(!empty($data['city']) ? ', '.$data['city'] : '')) ?: null,
                'ud_country_id' => $data['country'] ?? null,
                'ud_state_id' => $data['state'] ?? null,
                'ud_pincode' => $data['pincode'] ?? null,
            ]);

            return $registrationNumber;
        });

        return redirect()->route('join.create')->with('registration_success', $registrationNumber);
    }

    private function viewData(): array
    {
        $generalSetting = GeneralSetting::find(1);
        $registrationSettings = PublicRegistrationSetting::current();

        return [
            'title' => 'Join Net-Works',
            'generalSetting' => $generalSetting,
            'registrationOpen' => $registrationSettings->enabled,
            'registrationSettings' => $registrationSettings,
            'countries' => Country::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'defaultCountryId' => Country::where('name', 'India')->value('id'),
            'headerNavigation' => $this->navigationFor('header'),
            'footerNavigation' => $this->navigationFor('footer'),
        ];
    }

    private function navigationFor(string $location)
    {
        return Page::with(['children' => fn ($query) => $query->where('status', 1)->whereIn('nav_location', [$location, 'both'])])
            ->whereNull('parent_id')->where('status', 1)->whereIn('nav_location', [$location, 'both'])
            ->orderBy('nav_order')->orderBy('page_name')->get();
    }
}
