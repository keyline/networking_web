<?php

namespace App\Models\User;

use App\Models\BaseModel;
use App\Models\Business\BusinessMaster;
use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\Industry\IndustryMaster;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserToCompanies extends BaseModel
{
    use HasFactory;

    protected $table = 'user_companies_map';

    protected $guarded = [];

    protected $primaryKey = 'ucm_id';

    public $timestamps = false;

    #____________________________ Relationships ____________________________

    public function companie()
    {
        return $this->belongsTo(CompaniesDetail::class,'ucm_cmp_id','cmpd_cmp_id');
    }
}
