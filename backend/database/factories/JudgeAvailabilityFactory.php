<?php

namespace Database\Factories;

use App\Models\JudgeAvailability;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JudgeAvailabilityFactory extends Factory
{
    protected $model = JudgeAvailability::class;

    public function definition(): array
    {
        return [
            'judge_id' => User::factory()->state(['role' => 'judge']),
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_available' => true,
        ];
    }
}
