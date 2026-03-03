<?php

namespace Database\Factories;

use App\Models\CourtCase;
use App\Models\Hearing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HearingFactory extends Factory
{
    protected $model = Hearing::class;

    public function definition(): array
    {
        $date = now()->addDays(2)->toDateString();

        return [
            'case_id' => CourtCase::factory(),
            'judge_id' => User::factory()->state(['role' => 'judge']),
            'courtroom' => fake()->randomElement(['Courtroom A', 'Courtroom B', 'Courtroom C']),
            'hearing_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => 'scheduled',
        ];
    }
}
