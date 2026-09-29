<?php

namespace App\Models\Accounting;

use App\Models\MemberMembership;
use App\Models\User\UserMaster;
use Illuminate\Database\Eloquent\Model;

class MembershipInvoice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'invoice_date' => 'date', 'due_date' => 'date', 'period_start' => 'date',
        'period_end' => 'date', 'issued_at' => 'datetime', 'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2', 'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2', 'paid_amount' => 'decimal:2',
    ];

    public function membership()
    {
        return $this->belongsTo(MemberMembership::class);
    }

    public function user()
    {
        return $this->belongsTo(UserMaster::class, 'user_id', 'um_id');
    }

    public function items()
    {
        return $this->hasMany(MembershipInvoiceItem::class, 'invoice_id');
    }

    public function allocations()
    {
        return $this->hasMany(MembershipPaymentAllocation::class, 'invoice_id');
    }

    public function getBalanceDueAttribute(): string
    {
        return number_format(max(0, (float) $this->total_amount - (float) $this->paid_amount), 2, '.', '');
    }
}
