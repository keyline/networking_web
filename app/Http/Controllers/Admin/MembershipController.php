<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\MembershipSetting;
use App\Models\User\UserMaster;
use App\Services\MembershipRenewalCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->startOfDay();
        $baseMembers = UserMaster::query()->where('um_status', '!=', 3);
        $data['stats'] = [
            'total' => (clone $baseMembers)->count(),
            'active' => (clone $baseMembers)->whereHas('membership', fn ($q) => $q->where('membership_status', 'active'))->count(),
            'due_soon' => (clone $baseMembers)->whereHas('membership', fn ($q) => $q
                ->where('membership_status', 'active')
                ->whereBetween('renewal_date', [$today, $today->copy()->addDays(30)]))->count(),
            'pending' => (clone $baseMembers)->where(function ($q) {
                $q->whereDoesntHave('membership')
                    ->orWhereHas('membership', fn ($membership) => $membership->where('membership_status', 'pending'));
            })->count(),
        ];

        $query = UserMaster::with(['userDetail', 'userType', 'membership.plan'])
            ->where('um_status', '!=', 3);

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('um_user_name', 'like', "%{$search}%")
                    ->orWhere('um_email_id', 'like', "%{$search}%")
                    ->orWhere('um_mobile_no', 'like', "%{$search}%")
                    ->orWhereHas('userDetail', function ($details) use ($search) {
                        $details->where('ud_first_name', 'like', "%{$search}%")
                            ->orWhere('ud_last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->query('status')) {
            $query->where(function ($builder) use ($status) {
                $builder->whereHas('membership', fn ($membership) => $membership->where('membership_status', $status));

                if ($status === 'pending') {
                    $builder->orWhereDoesntHave('membership');
                }
            });
        }

        $data['members'] = $query->orderByDesc('um_id')->paginate(25)->withQueryString();
        $data['filters'] = $request->only(['search', 'status']);

        echo $this->admin_after_login_layout('Memberships', 'membership.index', $data);
    }

    public function edit(Request $request, UserMaster $user, MembershipRenewalCalculator $renewalCalculator)
    {
        $membership = MemberMembership::firstOrNew(['user_id' => $user->um_id]);

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'registration_date' => ['required', 'date'],
                'plan_id' => ['required', Rule::exists('membership_plans', 'id')->where('is_active', 1)],
                'payment_date' => ['nullable', 'date'],
                'payment_amount' => ['nullable', 'numeric', 'min:0'],
                'payment_method' => ['nullable', 'string', 'max:50'],
                'payment_reference' => ['nullable', 'string', 'max:150'],
                'membership_status' => ['required', Rule::in(['pending', 'active', 'expired', 'cancelled'])],
                'notes' => ['nullable', 'string', 'max:2000'],
            ]);

            $plan = MembershipPlan::findOrFail($validated['plan_id']);
            $settings = MembershipSetting::firstOrCreate(['id' => 1], ['renewal_basis' => 'joining_date']);
            $validated['renewal_date'] = $renewalCalculator->nextDate(
                Carbon::parse($validated['registration_date']),
                $plan->duration_months,
                $settings->renewal_basis,
                (int) ($settings->cycle_start_month ?? 1),
                (int) ($settings->cycle_day ?? 1)
            );
            $validated['payment_amount'] ??= $plan->fee;

            $validated['updated_by'] = auth('admin')->id();
            if (! $membership->exists) {
                $validated['created_by'] = auth('admin')->id();
            }

            $membership->fill($validated);
            $membership->user_id = $user->um_id;
            $membership->save();

            return redirect('admin/memberships')->with('success_message', 'Membership updated successfully.');
        }

        $user->load(['userDetail', 'userType']);
        $plans = MembershipPlan::where('is_active', true)->orderBy('duration_months')->get();
        $membershipSetting = MembershipSetting::firstOrCreate(['id' => 1], ['renewal_basis' => 'joining_date']);
        $data = compact('user', 'membership', 'plans', 'membershipSetting');

        echo $this->admin_after_login_layout('Manage Membership', 'membership.edit', $data);
    }

}
