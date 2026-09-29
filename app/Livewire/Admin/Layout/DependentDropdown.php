<?php

namespace App\Livewire\Admin\Layout;

use App\Models\Country;
use App\Models\District;
use App\Models\State;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DependentDropdown extends Component
{
    public $countries = [];
    public $states = [];
    public $districts = [];

    // Selected IDs with defaults
    public $selectedCountry = null;
    public $selectedState = null;
    public $selectedDistrict = null;

    /**
     * Mount the component with optional pre-selected values.
     *
     * @param  int|null  $c  Pre-selected Country ID
     * @param  int|null  $s  Pre-selected State ID
     * @param  int|null  $d  Pre-selected District ID
     */
    public function mount($c = null, $s = null, $d = null)
    {
        // Load all countries for the dropdown.
        $this->countries = Country::select('id', 'name')->get();

        // If a country is provided, set it and load its states.
        if ($c) {
            $this->selectedCountry = $c;
            $this->states = State::where('country_id', $c)->get();
        }

        // If a state is provided, set it and load its districts.
        if ($s) {
            $this->selectedState = $s;
            $this->districts = District::where('state_id', $s)->get();
        }

        // If a district is provided, set it.
        if ($d) {
            $this->selectedDistrict = $d;
        }
    }

    public function countryChanged($countryId)
    {
        $this->updatedSelectedCountry($countryId);
    }

    public function stateChanged($stateId)
    {
        $this->updatedSelectedState($stateId);
    }

    /**
     * Update states when the selected country changes.
     *
     * @param  int  $countryId
     */
    public function updatedSelectedCountry($countryId)
    {

        Log::info('Country updated: ' . $countryId);
        $this->states = State::where('country_id', $countryId)->get();
        $this->selectedState = null;
        $this->districts = [];
        $this->selectedDistrict = null;
    }

    /**
     * Update districts when the selected state changes.
     *
     * @param  int  $stateId
     */
    public function updatedSelectedState($stateId)
    {
        $this->districts = District::where('state_id', $stateId)->get();
        $this->selectedDistrict = null;
    }

    public function render()
    {
        return view('livewire.admin.layout.dependent-dropdown');
    }
}
