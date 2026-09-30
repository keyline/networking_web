<?php

namespace App\Http\Controllers;

use App\Models\Companies\CompaniesDetail;
use App\Models\Enquiries\EnquiryMaster;
use App\Models\BusinessPortfolio;
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
    public function show(string $slug): View
    {
        $business = CompaniesDetail::query()
            ->with(['companies.categories', 'companies.users.userDetail', 'gallery'])
            ->where('public_slug', $slug)
            ->where('cmpd_status', 1)
            ->firstOrFail();

        $social = DB::table('company_sociallink')->where('cs_cmp_id', $business->cmpd_cmp_id)->first();
        $portfolioModel = BusinessPortfolio::where('company_id', $business->cmpd_cmp_id)->where('is_published', true)->first();
        $portfolio = $portfolioModel?->published_snapshot;

        return view('front.business-profile', compact('business', 'social', 'portfolio'));
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
            'subject' => ['required', 'string', 'max:250'],
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
                'enm_question_for' => $business->cmpd_name,
                'enm_subject' => $data['subject'],
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
