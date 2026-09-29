<?php

namespace App\Models\Enquiries;

use App\Models\BaseModel;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserMaster;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class EnquiryMaster extends BaseModel
{
    use HasFactory;
    use Notifiable;

    protected $table = 'enquiry_master';

    protected $guarded = [];

    protected $primaryKey = 'enm_id';

    //public $timestamps = true;


    public const CREATED_AT = 'enm_created_at';
    public const UPDATED_AT = 'enm_updated_at';

    public function users()
    {
        return $this->belongsToMany(UserMaster::class, 'enquiry_to_user', 'etu_enm_id', 'etu_um_id', 'etu_cmp_id');
    }

    public function companies()
    {

        return $this->belongsToMany(CompaniesMaster::class, 'enquiry_to_user', 'etu_enm_id', 'etu_cmp_id');

    }
}
