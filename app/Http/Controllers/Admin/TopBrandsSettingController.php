<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Services\TopBrandsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Saves the ranking rule for the app's "Top Brands" grid. */
class TopBrandsSettingController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'top_brands_metric' => ['required', Rule::in(array_keys(TopBrandsService::METRICS))],
            'top_brands_days'   => ['required', 'integer', Rule::in(array_keys(TopBrandsService::PERIODS))],
            'top_brands_limit'  => ['required', 'integer', 'between:3,30'],
        ]);

        GeneralSetting::where('id', 1)->update($validated);

        return redirect('admin/settings#tab12')->with('success_message', 'Top Brands settings updated successfully.');
    }
}
