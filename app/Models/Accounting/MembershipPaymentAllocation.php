<?php

namespace App\Models\Accounting;

use App\Models\Accounting\Concerns\ImmutableAccountingRecord;
use Illuminate\Database\Eloquent\Model;

class MembershipPaymentAllocation extends Model
{
    use ImmutableAccountingRecord;

    protected $guarded = [];

    protected $casts = ['amount' => 'decimal:2'];

    public function payment()
    {
        return $this->belongsTo(MembershipPayment::class, 'payment_id');
    }

    public function invoice()
    {
        return $this->belongsTo(MembershipInvoice::class, 'invoice_id');
    }
}
