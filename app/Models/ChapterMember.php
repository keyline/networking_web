<?php

namespace App\Models;

use App\Models\User\UserMaster;
use App\Models\Companies\CompaniesMaster;
use Illuminate\Database\Eloquent\Model;

class ChapterMember extends Model
{
    protected $guarded = [];

    protected $casts = [
        'joined_on' => 'date',
        'left_on' => 'date',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function user()
    {
        return $this->belongsTo(UserMaster::class, 'user_id', 'um_id');
    }

    public function company()
    {
        return $this->belongsTo(CompaniesMaster::class, 'company_id', 'cmp_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return str($this->role)->replace('_', ' ')->title()->toString();
    }
}
