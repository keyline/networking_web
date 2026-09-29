<?php

namespace App\Livewire\Admin\Component;

use App\Models\Companies\CompaniesDetail;
use Livewire\Component;

class CompanyStatusToggle extends Component
{

    public $cmpId;
    public $cmpd_status;

    public function mount($cmpId)
    {
        $this->cmpId = $cmpId;
        $company = CompaniesDetail::select('cmpd_status')->where('cmpd_cmp_id', $this->cmpId)->firstOrFail();
        $this->cmpd_status = $company->cmpd_status;
    }

    public function toggleStatus()
    {
        $company = CompaniesDetail::where('cmpd_cmp_id', $this->cmpId)->firstOrFail();
        $company->update(['cmpd_status' => !$company->cmpd_status]);
        $this->cmpd_status = $company->cmpd_status;
    }

    public function render()
    {
        return view('livewire.admin.component.company-status-toggle');
    }
}
