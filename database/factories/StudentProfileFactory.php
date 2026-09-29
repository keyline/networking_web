<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentProfile>
 */
class StudentProfileFactory extends Factory
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
            'first_name'=> fake()->firstName,
            'last_name' => fake()->lastName,
            'full_name' => fake()->name,
            'profile_pic' => fake()->imageUrl(100, 100, 'dogs'),

        ];
    }
}
