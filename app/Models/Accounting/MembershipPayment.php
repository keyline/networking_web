<?php

namespace App\Models\Accounting;

use App\Models\Accounting\Concerns\ImmutableAccountingRecord;
use App\Models\MemberMembership;
use App\Models\User\UserMaster;
use Illuminate\Database\Eloquent\Model;

class MembershipPayment extends Model
{
    use ImmutableAccountingRecord;

    protected $guarded = [];

    protected $casts = ['payment_date' => 'date', 'amount' => 'decimal:2', 'posted_at' => 'datetime', 'metadata' => 'array'];

    public function membership()
    {
        return $this->belongsTo(MemberMembership::class);
    }

    public function user()
    {
        return $this->belongsTo(UserMaster::class, 'user_id', 'um_id');
    }

    public function allocations()
    {
        return $this->hasMany(MembershipPaymentAllocation::class, 'payment_id');
    }

    public function receipt()
    {
        return $this->hasOne(MembershipReceipt::class, 'payment_id');
    }

    public function getUnallocatedAmountAttribute(): string
    {
        return number_format(max(0, (float) $this->amount - (float) $this->allocations()->sum('amount')), 2, '.', '');
    }
}
