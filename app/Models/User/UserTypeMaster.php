<?php

namespace App\Models\User;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Illuminate\Database\Eloquent\Model;

class UserTypeMaster extends BaseModel
{
    use HasFactory;

    protected $table = 'user_type_master';

    protected $guarded = [];

    protected $primaryKey = 'utm_id';

    public $timestamps = false;

    #____________________________ Relationships ____________________________

    public function userMaster()
    {
        return $this->belongsTo(UserMaster::class, 'utm_id', 'um_utm_id');
    }

}
