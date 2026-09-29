<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;

class BusinessMaster extends BaseModel
{
    use HasFactory;

    protected $table = 'business_type_master';

    protected $guarded = [];

    protected $primaryKey = 'btm_id';

    public $timestamps = false;

    #____________________________ Relationships ____________________________
}
