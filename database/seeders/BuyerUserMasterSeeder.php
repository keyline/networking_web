<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Log;

class BuyerUserMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // User type: 1 (buyer) or 2 (seller)
        $user_type = 1; // (buyer)
        $user_number = 150; // Number of users to create
        $faker = Faker::create();


        for ($i = 0; $i < $user_number; $i++) {

            try {
                // Begin transaction
                DB::beginTransaction();

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
                    'ud_business_name' =>  null,
                    'ud_business_addr_1' => null,
                    'ud_business_addr_2' => null,
                    'ud_business_category' => null,
                    'ud_keywords' => null,
                    'um_profile_image' => $faker->imageUrl(),
                    'um_social_links' => json_encode([]),

                ];
                DB::table('user_details')->insert($userDetailsData);


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
