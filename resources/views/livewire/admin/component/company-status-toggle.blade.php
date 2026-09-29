<div>
    <style>
        .form-check-input.sm {
            width: 40px;
            height: 25px;
        }

        .form-check-input.sm:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
    </style>

    <div class="form-check form-switch">
        <input
            class="form-check-input sm"
            type="checkbox"
            role="switch"
            id="flexSwitchCheckDefault-{{ $cmpId }}"
            wire:model="cmpd_status"
            wire:change="toggleStatus"
            @checked($cmpd_status)
        >
    </div>
</div>
