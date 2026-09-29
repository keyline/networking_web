<?php

namespace App\Models\Review;

use App\Models\Companies\CompaniesDetail;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewMaster extends Model
{
    use HasFactory;


    protected $table = 'reviews';

    protected $guarded = [];

    protected $primaryKey = 'rev_id';

    public $timestamps = true;

    /**
     * Get the user details for this review.
     */
    public function user()
    {
        return $this->belongsTo(UserDetails::class, 'rev_um_id', 'ud_um_id');
    }

    /**
     * Optionally, get the business details for this review.
     */
    public function business()
    {
        return $this->belongsTo(CompaniesDetail::class, 'rev_cmp_id', 'cmpd_cmp_id');
    }
}
