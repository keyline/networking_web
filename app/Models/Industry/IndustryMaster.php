<?php

namespace App\Models\Industry;

use App\Models\BaseModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class IndustryMaster extends BaseModel
{
    use HasFactory;


    protected $table = 'industry_master';

    protected $guarded = [];

    protected $primaryKey = 'im_id';

    public $timestamps = false;

    #____________________________ Relationships ____________________________


}
