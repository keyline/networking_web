<?php

namespace App\Models\User;

use App\Models\BaseModel;
use App\Models\Review\ReviewMaster;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDetails extends BaseModel
{
    use HasFactory;

    protected $table = 'user_details';

    protected $guarded = [];

    protected $primaryKey = 'ud_id';

    //public $timestamps = false;

    public const CREATED_AT = 'ud_created_at';
    public const UPDATED_AT = 'ud_updated_at';

    #____________________________ Relationships ____________________________

    public function userMaster()
    {
        return $this->belongsTo(UserMaster::class, 'ud_um_id', 'um_id');
    }

    /**
     * Get all reviews written by the user.
     */
    public function reviews()
    {
        return $this->hasMany(ReviewMaster::class, 'rev_um_id', 'ud_um_id');
    }
}
