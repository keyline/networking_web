@php
    $fullName = trim(($user->userDetail->ud_first_name ?? '').' '.($user->userDetail->ud_last_name ?? ''));
    $displayName = $fullName ?: ($user->um_user_name ?: 'Member #'.$user->um_id);
    $registrationDate = old('registration_date', optional($membership->registration_date)->format('Y-m-d') ?: optional($user->um_created_at)->format('Y-m-d'));
    $cycleMonth = (int) ($membershipSetting->cycle_start_month ?? 1);
    $cycleDay = (int) ($membershipSetting->cycle_day ?? 1);
    $cycleAnchor = \Carbon\Carbon::create(2000, $cycleMonth, min($cycleDay, \Carbon\Carbon::create(2000, $cycleMonth, 1)->daysInMonth))->format('j F');
@endphp

<div class="page-header">
    <nav><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ url('admin/memberships') }}">Memberships</a></li><li class="breadcrumb-item active">Manage</li></ol></nav>
    <h1 class="page-header-title">Manage Membership</h1>
</div>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card">
            <div class="card-header">
                <div>
                    <h4 class="mb-1">{{ $displayName }}</h4>
                    <span class="text-muted">{{ $user->um_email_id }} · {{ $user->um_mobile_no }} · {{ $user->userType->utm_name ?? 'Member' }}</span>
                </div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <form method="POST" action="{{ url('admin/memberships/'.$user->um_id.'/edit') }}">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label" for="plan_id">Membership plan <span class="text-danger">*</span></label>
                            <select id="plan_id" name="plan_id" class="form-select" required>
                                <option value="">Select plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" data-fee="{{ $plan->fee }}" @selected((string) old('plan_id', $membership->plan_id) === (string) $plan->id)>{{ $plan->name }} — ₹{{ number_format((float) $plan->fee, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="registration_date">Registration date <span class="text-danger">*</span></label>
                            <input type="date" id="registration_date" name="registration_date" class="form-control" value="{{ $registrationDate }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="renewal_date">Renewal date</label>
                            <input type="date" id="renewal_date" class="form-control" value="{{ optional($membership->renewal_date)->format('Y-m-d') }}" readonly>
                            <small class="text-muted">Calculated automatically using {{ $membershipSetting->renewal_basis === 'calendar' ? 'the organization cycle anchored on '.$cycleAnchor : 'this member’s joining-date anniversary' }}.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="membership_status">Membership status <span class="text-danger">*</span></label>
                            <select id="membership_status" name="membership_status" class="form-select" required>
                                @foreach(['pending' => 'Pending', 'active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('membership_status', $membership->membership_status ?: 'pending') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="payment_date">Payment date</label>
                            <input type="date" id="payment_date" name="payment_date" class="form-control" value="{{ old('payment_date', optional($membership->payment_date)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="payment_amount">Payment amount</label>
                            <input type="number" min="0" step="0.01" id="payment_amount" name="payment_amount" class="form-control" value="{{ old('payment_amount', $membership->payment_amount) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="payment_method">Payment method</label>
                            <select id="payment_method" name="payment_method" class="form-select">
                                <option value="">Select method</option>
                                @foreach(['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank transfer', 'upi' => 'UPI', 'cheque' => 'Cheque', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('payment_method', $membership->payment_method) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="payment_reference">Payment reference / transaction ID</label>
                            <input type="text" id="payment_reference" name="payment_reference" maxlength="150" class="form-control" value="{{ old('payment_reference', $membership->payment_reference) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="4" maxlength="2000" class="form-control">{{ old('notes', $membership->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ url('admin/memberships') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save membership</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const plan = document.getElementById('plan_id');
    const amount = document.getElementById('payment_amount');
    plan.addEventListener('change', function () {
        const selected = plan.options[plan.selectedIndex];
        if (selected.dataset.fee && !amount.value) {
            amount.value = selected.dataset.fee;
        }
    });
});
</script>
