<?php

namespace App\Models\Accounting;

use App\Models\Accounting\Concerns\ImmutableAccountingRecord;
use Illuminate\Database\Eloquent\Model;

class MembershipAccountingAudit extends Model
{
    use ImmutableAccountingRecord;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['payload' => 'array', 'created_at' => 'datetime'];

    public function auditable()
    {
        return $this->morphTo();
    }
}
