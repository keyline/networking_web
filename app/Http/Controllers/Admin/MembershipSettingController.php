<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\MembershipSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MembershipSettingController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'renewal_basis' => ['required', Rule::in(['calendar', 'joining_date'])],
            'cycle_start_month' => ['required_if:renewal_basis,calendar', 'nullable', 'integer', 'between:1,12'],
            'cycle_day' => ['required_if:renewal_basis,calendar', 'nullable', 'integer', 'between:1,31'],
            'plans' => ['required', 'array'],
            'plans.*.fee' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'plans.*.is_active' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated) {
            MembershipSetting::query()->updateOrCreate(['id' => 1], [
                'renewal_basis' => $validated['renewal_basis'],
                'cycle_start_month' => $validated['cycle_start_month'] ?? 1,
                'cycle_day' => $validated['cycle_day'] ?? 1,
            ]);

            foreach (MembershipPlan::all() as $plan) {
                $input = $validated['plans'][$plan->id] ?? null;
                if ($input) {
                    $plan->update([
                        'fee' => $input['fee'],
                        'is_active' => (bool) ($input['is_active'] ?? false),
                    ]);
                }
            }
        });

        return redirect('admin/settings#tab11')->with('success_message', 'Membership settings updated successfully.');
    }
}
