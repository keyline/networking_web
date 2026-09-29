<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class MembershipInvoiceItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4', 'tax_amount' => 'decimal:2', 'line_total' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(MembershipInvoice::class, 'invoice_id');
    }
}
