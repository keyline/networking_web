<?php

namespace App\Imports;

use App\Models\Companies\CompaniesDetail;
use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserDetails;
use App\Models\User\UserMaster;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CompaniesImport implements ToModel, WithStartRow, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if ($row[3] != '' && $row[6] != '') {

            $user = UserMaster::firstOrCreate([
                        'um_email_id'   => !empty($row[6]) ? $row[6] : 'no-reply@net-works.local',
                        'um_mobile_no'  => $row[3]
                    ], [
                        'um_utm_id' => 2,
                        'um_email_id' => $row[6],
                        'um_mobile_no' => $row[3]

                    ]);


            $userId = "EN" . str_pad($user->um_id, 6, '0', STR_PAD_LEFT);

            $userPassword = Hash::make($userId);


            $user->um_user_name = $userId;
            $user->um_password = $userPassword;

            $user->save();




            $userDetails =  UserDetails::firstorCreate(
                ['ud_um_id' => $user->um_id], // Attribute(s) to check for existence
                [
                    'ud_um_id' => $user->um_id,
                    'ud_salutation' => $row[1],
                    'ud_first_name' => $row[2] ?? ''
                ]
            );

            $company = CompaniesMaster::create([

            ]);

            $companyDetail = CompaniesDetail::firstOrCreate([
                'cmpd_cmp_id'   => $company->cmp_id
            ], [
                'cmpd_cmp_id'   => $company->cmp_id,
                'cmpd_name'     => $row[15] ?? '',
                'cmpd_description' => $row[18] ?? '',
                'cmpd_address1'     => $row[10] ?? '',
                'cmpd_address2'     => $row[11] ?? '',
                'cmpd_address3'     => $row[12] ?? '',
                'cmpd_district'     => $row[13] ?? '',
                'cmpd_pincode'      => $row[14] ?? ''
            ]);

            $user->companies()->attach($company->cmp_id);


        }

    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
