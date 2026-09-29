<?php

namespace App\Models\User;

use App\Models\BaseModel;
use App\Models\Companies\CompaniesMaster;
use App\Models\Enquiries\EnquiryMaster;
use App\Models\MemberMembership;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
// class UserMaster extends BaseModel


class UserMaster extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'user_master';

    protected $guarded = [];

    protected $primaryKey = 'um_id';

    //public $timestamps = true;


    public const CREATED_AT = 'um_created_at';
    public const UPDATED_AT = 'um_updated_at';

    /**
     * Override getAuthPassword to use the custom password column.
     */
    public function getAuthPassword()
    {
        return $this->um_password;
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->um_email_id;
    }

    #____________________________ Relationships ____________________________

    public function userDetail()
    {
        return $this->hasOne(UserDetails::class, 'ud_um_id', 'um_id');
    }

    public function userType()
    {
        return $this->belongsTo(UserTypeMaster::class,'um_utm_id','utm_id');
    }

    public function companies()
    {
        return $this->belongsToMany(CompaniesMaster::class, 'user_companies_map', 'ucm_um_id', 'ucm_cmp_id');
    }


    public function companiesMap()
    {
        return $this->hasMany(UserToCompanies::class, 'ucm_um_id', 'um_id');
    }

    public function enquiries()
    {

        return $this->belongsToMany(EnquiryMaster::class, 'enquiry_to_user', 'etu_um_id', 'etu_enm_id');
    }

    public function membership()
    {
        return $this->hasOne(MemberMembership::class, 'user_id', 'um_id');
    }
}
