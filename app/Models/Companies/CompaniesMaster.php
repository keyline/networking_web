<?php

namespace App\Models\Companies;

use App\Models\BaseModel;
use App\Models\Business\BusinessCategoryMaster;
use App\Models\Enquiries\EnquiryMaster;
use App\Models\Industry\IndustryMaster;
use App\Models\User\UserMaster;
use App\Models\BusinessPortfolio;
use App\Models\BusinessPortfolioItem;
use App\Models\BusinessPortfolioMedia;
use App\Services\PortfolioRouteToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompaniesMaster extends BaseModel
{
    use HasFactory;

    protected $table = 'companies_master';

    protected $guarded = [];

    protected $primaryKey = 'cmp_id';

    public $timestamps = false;

    public const CREATED_AT = 'cmp_created_at';
    public const UPDATED_AT = 'cmp_updated_at';

    #____________________________ Relationships ____________________________

    public function companiesDetail()
    {
        return $this->hasOne(CompaniesDetail::class, 'cmpd_cmp_id', 'cmp_id');
    }

    public function industries()
    {
        return $this->belongsToMany(IndustryMaster::class);
    }

    public function users()
    {
        return $this->belongsToMany(UserMaster::class, 'user_companies_map', 'ucm_cmp_id', 'ucm_um_id');
    }


    public function details()
    {
        return $this->hasOne(CompaniesDetail::class, 'cmpd_cmp_id', 'cmp_id');
    }

    public function owner()
    {
        return $this->hasOne(UserToCompanies::class, 'ucm_cmp_id', 'cmp_id');
    }

    // public function owner()
    // {
    //     return $this->belongsTo(UserToCompanies::class, 'ucm_cmp_id'); // Adjust the foreign key as per your schema
    // }

    public function enquiries()
    {
        return $this->belongsToMany(EnquiryMaster::class, 'enquiry_to_user', 'etu_cmp_id', 'etu_um_id', 'etu_enm_id');
    }

    // If your Companies model has a relationship to categories, you might include it here as well
    public function categories()
    {
        return $this->belongsToMany(BusinessCategoryMaster::class, 'categories_to_companies', 'ctc_cmp_id', 'ctc_bcm_id'); // Adjust as necessary
    }

    public function portfolio()
    {
        return $this->hasOne(BusinessPortfolio::class, 'company_id', 'cmp_id');
    }

    public function portfolioItems()
    {
        return $this->hasMany(BusinessPortfolioItem::class, 'company_id', 'cmp_id')->orderBy('sort_order');
    }

    public function portfolioMedia()
    {
        return $this->hasMany(BusinessPortfolioMedia::class, 'company_id', 'cmp_id')->orderBy('sort_order');
    }

    public function getCmpNameAttribute(): string
    {
        return $this->details?->cmpd_name ?? 'Business';
    }

    public function portfolioRouteToken(): string
    {
        return app(PortfolioRouteToken::class)->encode((int) $this->cmp_id);
    }
}
