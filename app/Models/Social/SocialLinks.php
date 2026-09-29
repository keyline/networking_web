<?php

namespace App\Models\Social;

use App\Models\Companies\CompaniesDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLinks extends Model
{
    use HasFactory;

    protected $table = 'company_sociallink';

    protected $guarded = [];

    protected $primaryKey = 'cs_id';

    public $timestamps = true;

    public const CREATED_AT = 'cs_created_at';
    public const UPDATED_AT = 'cs_updated_at';

    #____________________________ Relationships ____________________________


    public function business()
    {
        return $this->belongsTo(CompaniesDetail::class, 'cs_cmp_id', 'cmpd_cmp_id');
    }
}
