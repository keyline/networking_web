<?php

namespace App\Models\Companies;

use App\Models\Business\BusinessCategoryMaster;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryToCompany extends Model
{
    use HasFactory;

    protected $table = 'categories_to_companies';

    protected $primaryKey = 'ctc_id';

    protected $guarded = [];

    public const CREATED_AT = 'ctc_created_at';
    public const UPDATED_AT = 'ctc_updated_at';

    public function category()
    {
        return $this->belongsTo(BusinessCategoryMaster::class, 'ctc_bcm_id', 'bcm_id');
    }
}
