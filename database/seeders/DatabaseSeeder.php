<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            AdminsTableSeeder::class,
            ServiceSeeder::class,
            ServiceTypeSeeder::class,
            ServiceAttributeSeeder::class,
            GeneralSettingSeeder::class,
        ]);

        //Creating 50 students
        //\App\Models\StudentProfile::factory(50)->create();

        //\App\Models\MentorProfile::factory(50)->create();
        \App\Models\User::factory(25)->create()->each(function ($user) {
            list($firstName, $lastName) = array_pad(explode(' ', trim($user->name)), 2, null);
            if($user->role  == '2') {
                //it's a mentor

                $user->mentorProfile()->save(\App\Models\MentorProfile::factory()->make(
                    [
                                    'user_id'   =>$user->id,
                                    'first_name'=> $firstName,
                                    'last_name' => $lastName,
                                    'display_name'=> $user->name,
                                ]
                ));


            } else {
                //it's a student
                $user->studentProfile()->save(\App\Models\StudentProfile::factory()->make(
                    [
                        'user_id'   => $user->id,
                        'first_name'=> $firstName,
                        'last_name' => $lastName,
                        'full_name'=> $user->name,
                    ]
                ));
            }

        });





    }
}
