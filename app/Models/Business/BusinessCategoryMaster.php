<?php
namespace App\Models\Business;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;
class BusinessCategoryMaster extends BaseModel
{
    use HasFactory;
    protected $table = 'business_category_master';
    protected $guarded = [];
    protected $primaryKey = 'bcm_id';
    public $timestamps = false;
    #____________________________ Relationships ____________________________
}
