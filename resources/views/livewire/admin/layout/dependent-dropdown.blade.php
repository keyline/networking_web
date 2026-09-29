<div>



    <!-- Country Dropdown -->
    <div class="row mb-3">
        <label for="country" class="col-md-2 col-lg-2 col-form-label">Country <span class="text-danger">*</span></label>

        <div class="col-md-10 col-lg-10">
            <select wire:model="selectedCountry" name="country"  wire:change="countryChanged($event.target.value)" class="form-select" required>
                <option value="">Select Country</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" {{ $selectedCountry == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- State Dropdown -->
    @if (!empty($states))
        <div class="row mb-3">
            <label for="state" class="col-md-2 col-lg-2 col-form-label">State <span class="text-danger">*</span></label>
            <div class="col-md-10 col-lg-10">
                <select wire:model="selectedState" name="state" wire:change="stateChanged($event.target.value)" class="form-select" required>
                    <option value="">Select State</option>
                    @foreach ($states as $state)
                        <option value="{{ $state->id }}" {{ $selectedState == $state->id ? 'selected' : '' }}>
                            {{ $state->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    <!-- District Dropdown -->
    @if (!empty($districts))
        <div class="row mb-3">
            <label for="district" class="col-md-2 col-lg-2 col-form-label">District <span class="text-danger">*</span></label>
            <div class="col-md-10 col-lg-10">
                <select wire:model="selectedDistrict" name="district" class="form-select" required>
                    <option value="">Select District</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}" {{ $selectedDistrict == $district->id ? 'selected' : '' }}>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif
</div>
