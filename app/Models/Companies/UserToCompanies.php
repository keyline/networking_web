<?php

namespace App\Models\Companies;

use App\Models\Business\BusinessCategoryMaster;
use App\Models\User\UserDetails;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToCompanies extends Model
{
    use HasFactory;

    protected $table = 'user_companies_map';

    protected $guarded = [];

    protected $primaryKey = 'ucm_id';

    public $timestamps = false;

    #____________________________ Relationships ____________________________

    public function companies()
    {
        return $this->belongsTo(CompaniesMaster::class, 'ucm_cmp_id');
    }

    public function details()
    {
        return $this->hasOne(CompaniesDetail::class, 'cmpd_cmp_id', 'ucm_cmp_id');
    }


    public function userDtl()
    {
        return $this->hasOne(UserDetails::class, 'ud_um_id', 'ucm_um_id');
    }


}
