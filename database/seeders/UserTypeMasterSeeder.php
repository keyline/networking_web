<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['utm_name' => 'Buyer'],
            ['utm_name' => 'Seller'],
        ];

        DB::table('user_type_master')->insert($data);
    }
}
