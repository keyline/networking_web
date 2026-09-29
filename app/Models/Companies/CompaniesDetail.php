<?php

namespace App\Models\Companies;

use App\Models\Business\BusinessCategoryMaster;
use App\Models\Country;
use App\Models\District;
use App\Models\Review\ReviewMaster;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompaniesDetail extends Model
{
    use HasFactory;

    protected $table = 'companies_details';

    protected $primaryKey = 'cmpd_id';

    public const CREATED_AT = 'cmpd_created_at';
    public const UPDATED_AT = 'cmpd_updated_at';

    protected $guarded = [];


    public function companies()
    {
        return $this->belongsTo(CompaniesMaster::class, 'cmpd_cmp_id', 'cmp_id');
    }


    public function district()
    {
        return $this->belongsTo(District::class, 'cmpd_district', 'id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'cmpd_state', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'cmpd_country', 'id');
    }


    public function reviews()
    {
        return $this->hasMany(ReviewMaster::class, 'rev_cmp_id', 'cmpd_cmp_id');
    }

    public function gallery()
    {
        return $this->hasMany(CompanyImages::class, 'ci_cmp_id', 'cmpd_cmp_id');
    }
}
