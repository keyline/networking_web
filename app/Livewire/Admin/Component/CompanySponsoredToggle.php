<?php

namespace App\Livewire\Admin\Component;

use App\Models\Companies\CompaniesDetail;
use Livewire\Component;

/** Admin switch that features a business in the app's home "Sponsored" row. */
class CompanySponsoredToggle extends Component
{
    public $cmpId;
    public $cmpd_is_sponsored;

    public function mount($cmpId)
    {
        $this->cmpId = $cmpId;
        $company = CompaniesDetail::select('cmpd_is_sponsored')->where('cmpd_cmp_id', $this->cmpId)->firstOrFail();
        $this->cmpd_is_sponsored = (bool) $company->cmpd_is_sponsored;
    }

    public function toggleSponsored()
    {
        $company = CompaniesDetail::where('cmpd_cmp_id', $this->cmpId)->firstOrFail();
        $company->update(['cmpd_is_sponsored' => !$company->cmpd_is_sponsored]);
        $this->cmpd_is_sponsored = (bool) $company->cmpd_is_sponsored;
    }

    public function render()
    {
        return view('livewire.admin.component.company-sponsored-toggle');
    }
}
