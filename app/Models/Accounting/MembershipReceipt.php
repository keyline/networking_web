<?php

namespace App\Models\Accounting;

use App\Models\Accounting\Concerns\ImmutableAccountingRecord;
use Illuminate\Database\Eloquent\Model;

class MembershipReceipt extends Model
{
    use ImmutableAccountingRecord;

    protected $guarded = [];

    protected $casts = ['receipt_date' => 'date', 'amount' => 'decimal:2', 'issued_at' => 'datetime'];

    public function payment()
    {
        return $this->belongsTo(MembershipPayment::class, 'payment_id');
    }
}
