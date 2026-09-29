<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Log;

class SellerUserMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // User type: 1 (buyer) or 2 (seller)
        $user_type = 2; // Change this for different user types
        $user_number = 30; // Number of users to create
        $faker = Faker::create();

        // Fetch data from industry_master and business_type_master
        $industry_master = DB::table('industry_master')->select('im_id', 'im_name', 'im_tag')->get()->toArray();
        $business_type_master = DB::table('business_type_master')->select('btm_id', 'btm_name')->get()->toArray();

        // Validate fetched data
        if (empty($industry_master) || empty($business_type_master)) {
            throw new \Exception('Industry Master or Business Type Master data is missing.');
        }

        for ($i = 0; $i < $user_number; $i++) {

            try {
                // Begin transaction
                DB::beginTransaction();
                // Generate random indices for industry and business types
                $industry_index = rand(0, count($industry_master) - 1);
                $business_index = rand(0, count($business_type_master) - 1);

                // Create user_master record
                $userMasterData = [
                    'um_utm_id' => $user_type,
                    'um_password' => bcrypt('password'),
                    'um_remember_token' => \Illuminate\Support\Str::random(10),
                    'um_is_change_password' => $faker->boolean(),
                    'um_created_at' => now(),
                    'um_status' => 1,
                ];
                $id = DB::table('user_master')->insertGetId($userMasterData);

                // Create user_details record
                $userDetailsData = [
                    'um_id' => $id,
                    'ud_aadhar_no' => $faker->unique()->numerify('##########'),
                    'ud_td_lic_file' => $faker->word . '.pdf',
                    'ud_gst_cert' => $faker->word . '.pdf',
                    'ud_first_name' => $faker->firstName,
                    'ud_last_name' => $faker->lastName,
                    'ud_mobile_no' => $faker->phoneNumber,
                    'ud_whatsapp_no' => $faker->phoneNumber,
                    'ud_email_id' => $faker->unique()->safeEmail,
                    'ud_addr_1' => $faker->address,
                    'ud_addr_2' => $faker->secondaryAddress,
                    'ud_business_name' => $user_type == 2 ? $faker->company : null,
                    'ud_business_addr_1' => $user_type == 2 ? $faker->address : null,
                    'ud_business_addr_2' => $user_type == 2 ? $faker->secondaryAddress : null,
                    'ud_business_category' => $user_type == 2 ? $business_type_master[$business_index]->btm_id : null,
                    'ud_keywords' => $user_type == 2 ? $faker->word : null,
                    'um_profile_image' => $faker->imageUrl(),
                    'um_social_links' => json_encode([
                        'facebook' => $faker->url,
                        'twitter' => $faker->url,
                        'linkedin' => $faker->url,
                    ]),

                ];
                DB::table('user_details')->insert($userDetailsData);

                // Handle companies and user_companies_map for sellers
                if ($user_type == 2) {
                    // Create companies_master record
                    $companiesData = [
                        'cmp_company_no' => $faker->unique()->numerify('CMP####'),
                        'cmp_name' => $faker->company,
                        'cmp_email' => $faker->unique()->safeEmail,
                        'cmp_alternate_email' => $faker->unique()->safeEmail,
                        'cmp_phone' => $faker->phoneNumber,
                        'cmp_whatsapp_no' => $faker->phoneNumber,
                        'cmp_logo' => $faker->imageUrl(),
                        'cmp_address1' => $faker->address,
                        'cmp_address2' => $faker->secondaryAddress,
                        'cmp_start_date' => $faker->date(),
                        'cmp_end_date' => $faker->date(),
                        'cmp_license_no' => $faker->numerify('L####'),
                        'cmp_last_renewal_date' => $faker->date(),
                        'cmp_status' => 1,
                    ];
                    $cmp_id = DB::table('companies_master')->insertGetId($companiesData);

                    // Create user_companies_map record
                    DB::table('user_companies_map')->insert([
                        'ucm_cmp_id' => $cmp_id,
                        'ucm_um_id' => $id,
                        'ucm_im_id' => $industry_master[$industry_index]->im_id,
                        'ucm_btm_id' => $business_type_master[$business_index]->btm_id,
                    ]);
                }
                // Commit the transaction
                DB::commit();
            } catch (Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                Log::error('Error seeding user data: ' . $e->getMessage());
            }
        }
    }
}
