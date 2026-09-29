<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessTypeMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['btm_name' => 'Proprietorship'],
            ['btm_name' => 'Partnership'],
            ['btm_name' => 'Company'],
            ['btm_name' => 'LLC'],
            ['btm_name' => 'LLP'],
            ['btm_name' => 'Public'],
        ];

        DB::table('business_type_master')->insert($data);
    }
}
