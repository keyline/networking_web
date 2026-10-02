<?php

namespace App\Models;

use App\Models\User\UserMaster;
use Illuminate\Database\Eloquent\Model;

class MemberMeeting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meeting_at' => 'datetime',
        'follow_up_on' => 'date',
    ];

    public function reporter()
    {
        return $this->belongsTo(UserMaster::class, 'reported_by_um_id', 'um_id');
    }

    public function counterpart()
    {
        return $this->belongsTo(UserMaster::class, 'counterpart_um_id', 'um_id');
    }

    public function inviter()
    {
        return $this->belongsTo(UserMaster::class, 'invited_by_um_id', 'um_id');
    }
}
