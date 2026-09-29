<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServiceAttribute;

class ServiceAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes= array(
            [
                'title'         => '1:1 Session',
                'description'   => 'this would be a long description',
                'slug'          => '1-1session',
                'duration'      => '60',
                'actual_amount' => '500',
                'slashed_amount'=> '500'
            ],
            [
                'title'         => 'Quick Chat',
                'description'   => 'this would be a long description for quick chat',
                'slug'          => 'quick-chat',
                'duration'      => '10',
                'actual_amount' => '50',
                'slashed_amount'=> '50'
            ],
            [
                'title'         => 'Doubt Session',
                'description'   => 'this would be a long description for doubt session',
                'slug'          => 'doubt-session',
                'duration'      => '60',
                'actual_amount' => '500',
                'slashed_amount'=> '500'
            ],
            [
                'title'         => 'Discovery Call',
                'description'   => 'this would be a long description for discovery call',
                'slug'          => 'discovery-call',
                'duration'      => '15',
                'actual_amount' => '50',
                'slashed_amount'=> '50'
            ],

            [
                'title'         => 'Mock Interview',
                'description'   => 'this would be a long description for mock interview',
                'slug'          => 'mock-interview',
                'duration'      => '45',
                'actual_amount' => '500',
                'slashed_amount'=> '500',
            ],

        );

        foreach ($attributes as $attribute) {
            # code...
            ServiceAttribute::create($attribute);
        }
    }
}
