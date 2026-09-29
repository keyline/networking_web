<?php

namespace Database\Factories;

use App\Models\MentorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MentorProfile>
 */
class MentorProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'   => \App\Models\User::factory(),
            'first_name' => fake()->firstName,
            'last_name'     => fake()->lastName,
            'display_name'  => fake()->name,
            'mobile'        => fake()->unique()->numerify('##########'),
            'social_url'    => fake()->url(),
            'title'         => fake()->sentence,
            'description'   => fake()->text,
            'timezone'      => 'Asia/Kolkata',
            'profile_pic'   => fake()->imageUrl(100, 100, 'cats'),
            'has_synced_calender'=> rand(0, 1),
            'full_name' => fake()->name,
            'google_mail'   => fake()->unique()->safeEmail(),
            'buffer'        => rand(60, 3600),
            'use_google_meet'   => rand(0, 1),
            'is_email_verified' => rand(0, 1),
            'service_added'     => rand(0, 1),
            'slots_added'       => rand(0, 1),
            'profile_completion'    =>rand(0, 1),
            'profile_shared'        =>rand(0, 1),
            'subscribe_to_whatsapp' =>rand(0, 1),
            'account_details_added' =>rand(0, 1),
            'personal_meeting_url'  =>fake()->url(),
            'signup_source'         =>fake()->sentence(5),
            'ratings_count'         =>rand(1, 5),
            'booking_count'         =>rand(0, 100),
            'upcoming_calls'        =>rand(0, 10),
            'booking_period'        =>rand(0, 15),


        ];
    }
}
