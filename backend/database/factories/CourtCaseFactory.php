<?php

namespace Database\Factories;

use App\Models\CourtCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourtCaseFactory extends Factory
{
    protected $model = CourtCase::class;

    public function definition(): array
    {
        return [
            'case_number' => sprintf('LF-%s-%05d', now()->format('Y'), fake()->unique()->numberBetween(1, 99999)),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'case_type' => fake()->randomElement(['civil', 'criminal', 'traffic']),
            'urgency_level' => fake()->numberBetween(1, 5),
            'complexity_level' => null,
            'estimated_duration' => fake()->randomFloat(2, 0.5, 6.0),
            'priority_score' => 1.20,
            'track' => fake()->randomElement(['fast', 'standard', 'complex']),
            'status' => fake()->randomElement(['pending', 'scheduled', 'completed']),
            'assigned_judge_id' => User::factory()->state(['role' => 'judge']),
        ];
    }
}
