<?php

namespace App\Services;

use App\Models\User\UserMaster;
use Illuminate\Database\Eloquent\Builder;

class UserService
{
    public function userExistsByEmail($email)
    {
        $user = UserMaster::join('user_details', 'user_master.um_id', '=', 'user_details.ud_um_id')
            ->where('user_master.um_email_id', $email)
            ->select('user_master.*') // or select specific columns
            ->first(); // use first() to get a single result

        return $user !== null; // return true if found, otherwise false
    }

    public function userExistsByPhoneNumber($phoneNo)
    {

        $user = UserMaster::join('user_details', 'user_master.um_id', '=', 'user_details.ud_um_id')
                    ->where('user_master.um_mobile_no', $phoneNo)
                    ->select('user_master.*') // or select specific columns
                    ->first(); // use first() to get a single result

        return $user !== null; // return true if found, otherwise false


    }

    public function getUserByEmailOrUserName($emailorUserName = "")
    {

        return UserMaster::with(['userDetail' => function ($query) {
            $query->select('ud_um_id', 'ud_first_name', 'ud_profile_image', 'ud_is_bengali'); // specify fields from user_details
        }])
                                 ->where('um_email_id', $emailorUserName)
                                 ->orWhere('um_user_name', $emailorUserName)
                                 ->select('um_id', 'um_email_id', 'um_otp') // specify fields from users
                                 ->first();


    }

    public function RegisteredUserExistByEmailOrMobileNo($email, $mobileNo)
    {

        $user = UserMaster::join('user_details', 'user_master.um_id', '=', 'user_details.ud_um_id')
    ->where(function (Builder $query) use ($email, $mobileNo) {
        $query->where('user_master.um_email_id', $email)
              ->orWhere('user_master.um_mobile_no', $mobileNo);
    })
    ->where('user_master.um_status', 2)
    ->select('user_master.*')
    ->first();
        // or select specific columns
        //->first(); // use first() to get a single result



        return $user !== null; // return true if found, otherwise false


    }
}
