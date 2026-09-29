<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    protected $guarded = [];
    protected $casts = ['fee' => 'decimal:2', 'is_active' => 'boolean'];
}
