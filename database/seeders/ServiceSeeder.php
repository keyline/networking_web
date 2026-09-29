<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services= array(
            [
            'name'      => 'Mental Health',
            'slug'      => 'mental-health',
            'description'=> 'this is test data for metal health',
            'image'     => '',
            'mentor_bg_color'=> '',
            ],
            [
            'name'      => 'Career Counselling',
            'slug'      => 'career-counselling',
            'description'=> 'this is test data for career conselling',
            'image'     => '',
            'mentor_bg_color'=> '',
            ],


        );

        foreach ($services as $service) {
            # code...
            Service::create($service);
        }
    }
}
