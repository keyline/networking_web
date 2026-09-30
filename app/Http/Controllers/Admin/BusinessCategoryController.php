<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business\BusinessCategoryMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $categories = BusinessCategoryMaster::query()
            ->withCount('companies')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $mergeCategories = BusinessCategoryMaster::query()->orderBy('name')->get(['bcm_id', 'name']);
        $summary = BusinessCategoryMaster::query()
            ->selectRaw('COUNT(*) AS total, SUM(status = 1) AS active')
            ->first();
        $assigned = DB::table('categories_to_companies')->distinct()->count('ctc_bcm_id');

        return $this->admin_after_login_layout(
            'Business Categories',
            'business-categories.index',
            compact('categories', 'mergeCategories', 'summary', 'assigned', 'search')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:250', Rule::unique('business_category_master', 'name')],
        ]);

        BusinessCategoryMaster::create([
            'parent_id' => 0,
            'name' => trim($data['name']),
            'slug' => $this->uniqueSlug($data['name']),
            'status' => 1,
            'created_by' => (int) session('user_id', 0),
            'updated_by' => (int) session('user_id', 0),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Business category added.');
    }

    public function update(Request $request, BusinessCategoryMaster $businessCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:250',
                Rule::unique('business_category_master', 'name')->ignore($businessCategory->bcm_id, 'bcm_id'),
            ],
            'status' => ['required', Rule::in(['0', '1', 0, 1])],
        ]);

        $businessCategory->update([
            'name' => trim($data['name']),
            'slug' => $this->uniqueSlug($data['name'], $businessCategory->bcm_id),
            'status' => (int) $data['status'],
            'updated_by' => (int) session('user_id', 0),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Business category updated.');
    }

    public function merge(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_category_id' => ['required', 'integer', 'exists:business_category_master,bcm_id'],
            'target_category_id' => [
                'required', 'integer', 'different:source_category_id', 'exists:business_category_master,bcm_id',
            ],
        ]);

        $sourceId = (int) $data['source_category_id'];
        $targetId = (int) $data['target_category_id'];

        DB::transaction(function () use ($sourceId, $targetId) {
            $source = BusinessCategoryMaster::query()->lockForUpdate()->findOrFail($sourceId);
            $target = BusinessCategoryMaster::query()->lockForUpdate()->findOrFail($targetId);

            $alreadyAssigned = DB::table('categories_to_companies')
                ->where('ctc_bcm_id', $targetId)
                ->pluck('ctc_cmp_id');

            if ($alreadyAssigned->isNotEmpty()) {
                DB::table('categories_to_companies')
                    ->where('ctc_bcm_id', $sourceId)
                    ->whereIn('ctc_cmp_id', $alreadyAssigned)
                    ->delete();
            }

            DB::table('categories_to_companies')
                ->where('ctc_bcm_id', $sourceId)
                ->update(['ctc_bcm_id' => $targetId, 'ctc_updated_at' => now()]);
            DB::table('user_details')
                ->where('ud_business_category', (string) $sourceId)
                ->update(['ud_business_category' => (string) $targetId, 'ud_updated_at' => now()]);
            BusinessCategoryMaster::query()
                ->where('parent_id', $sourceId)
                ->update(['parent_id' => $targetId, 'updated_at' => now()]);

            $source->delete();
            $target->update(['updated_by' => (int) session('user_id', 0), 'updated_at' => now()]);
        });

        return back()->with('success', 'Categories merged and all business assignments moved successfully.');
    }

    public function destroy(BusinessCategoryMaster $businessCategory): RedirectResponse
    {
        return DB::transaction(function () use ($businessCategory) {
            $businessCategory = BusinessCategoryMaster::query()->lockForUpdate()->findOrFail($businessCategory->bcm_id);
            $assignments = DB::table('categories_to_companies')
                ->where('ctc_bcm_id', $businessCategory->bcm_id)
                ->count();

            if ($assignments > 0) {
                return back()->withErrors([
                    'category' => "{$businessCategory->name} is assigned to {$assignments} business(es). Merge it into another category before deleting it.",
                ]);
            }

            DB::table('user_details')
                ->where('ud_business_category', (string) $businessCategory->bcm_id)
                ->update(['ud_business_category' => null, 'ud_updated_at' => now()]);
            BusinessCategoryMaster::query()
                ->where('parent_id', $businessCategory->bcm_id)
                ->update(['parent_id' => $businessCategory->parent_id, 'updated_at' => now()]);
            $businessCategory->delete();

            return back()->with('success', 'Unused business category deleted.');
        });
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;
        while (BusinessCategoryMaster::query()
            ->when($ignoreId, fn ($query) => $query->where('bcm_id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }
        return $slug;
    }
}
