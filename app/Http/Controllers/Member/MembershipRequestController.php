<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembershipRequestController extends Controller
{
    public function create(Request $request): View
    {
        return view('Member.Membership.request', [
            'plans' => MembershipPlan::where('is_active', true)->orderBy('duration_months')->get(),
            'membership' => $request->user('member')->membership,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user('member')->hasActiveBusinessMembership(), 422, 'Your Business Owner membership is already active.');
        $data = $request->validate([
            'plan_id' => ['required', Rule::exists('membership_plans', 'id')->where('is_active', true)],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', Rule::in(['cash', 'card', 'bank_transfer', 'upi', 'cheque', 'other'])],
            'payment_reference' => ['required', 'string', 'max:150'],
        ]);

        MemberMembership::updateOrCreate(['user_id' => $request->user('member')->um_id], $data + [
            'registration_date' => today(),
            'renewal_date' => null,
            'membership_status' => 'pending',
        ]);

        return redirect()->route('dashboard.index')->with('success', 'Business Owner request submitted for payment verification and admin approval.');
    }
}
