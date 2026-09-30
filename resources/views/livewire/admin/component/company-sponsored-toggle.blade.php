<div>
    <style>
        .form-check-input.sponsored-switch {
            width: 40px;
            height: 25px;
        }

        .form-check-input.sponsored-switch:checked {
            background-color: #e0a800;
            border-color: #e0a800;
        }
    </style>

    <div class="form-check form-switch" title="Show this business in the app's Sponsored row">
        <input
            class="form-check-input sponsored-switch"
            type="checkbox"
            role="switch"
            id="sponsoredSwitch-{{ $cmpId }}"
            wire:model="cmpd_is_sponsored"
            wire:change="toggleSponsored"
            @checked($cmpd_is_sponsored)
        >
    </div>
</div>
