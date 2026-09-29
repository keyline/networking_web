<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyImages extends Model
{
    use HasFactory;

    protected $table = 'company_images';

    protected $guarded = [];

    protected $primaryKey = 'ci_id';

    public $timestamps = true;

    public const CREATED_AT = 'ci_created_at';
    public const UPDATED_AT = 'ci_updated_at';

    #____________________________ Relationships ____________________________


    public function business()
    {
        return $this->belongsTo(CompaniesDetail::class, 'ci_cmp_id', 'cmpd_cmp_id');
    }
}
