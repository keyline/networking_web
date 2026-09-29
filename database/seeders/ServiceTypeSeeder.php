<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServiceType;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceTypes= array(

            [
                'name'  => '1:1 Session',
                'slug'  => '1-O-1-session',
                'description'=> '1:1 Session',
                'image'     => '',
            ],
            [
                'name'  => 'Priority DM',
                'slug'  => 'priority-dm',
                'description'=> 'Priority DM',
                'image'     => '',
            ],
            [
                'name'  => 'Group Sessio',
                'slug'  => 'group-session',
                'description'=> 'Group Session',
                'image'     => '',
            ],
            [
                'name'  => 'Discovery Call',
                'slug'  => 'discovery-call',
                'description'=> 'Discovery Call',
                'image'     => '',
            ],
        );

        foreach ($serviceTypes as $type) {
            # code...
            ServiceType::create($type);
        }
    }
}
