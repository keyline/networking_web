<?php

namespace App\Models;

use App\Models\User\UserMaster;
use Illuminate\Database\Eloquent\Model;

class MemberMembership extends Model
{
    protected $guarded = [];

    protected $casts = [
        'registration_date' => 'date',
        'renewal_date' => 'date',
        'payment_date' => 'date',
        'payment_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(UserMaster::class, 'user_id', 'um_id');
    }

    public function plan()
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id');
    }

    public function invoices()
    {
        return $this->hasMany(\App\Models\Accounting\MembershipInvoice::class, 'membership_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Accounting\MembershipPayment::class, 'membership_id');
    }
}
