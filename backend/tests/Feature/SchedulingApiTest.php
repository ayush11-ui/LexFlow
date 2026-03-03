<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\Hearing;
use App\Models\JudgeAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manually_schedule_case_without_conflict(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $judge = User::factory()->create(['role' => 'judge', 'specialization' => 'civil']);
        $case = CourtCase::factory()->create([
            'status' => 'pending',
            'case_type' => 'civil',
            'estimated_duration' => 1.00,
        ]);

        JudgeAvailability::factory()->create([
            'judge_id' => $judge->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_available' => true,
        ]);

        $token = $admin->createToken('test')->plainTextToken;
        $date = now()->addDay()->toDateString();
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/schedule/manual', [
                'case_id' => $case->id,
                'judge_id' => $judge->id,
                'courtroom' => 'Courtroom A',
                'hearing_date' => $date,
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]);

        $response->assertOk()->assertJsonPath('data.courtroom', 'Courtroom A');
        $this->assertDatabaseHas('cases', [
            'id' => $case->id,
            'status' => 'scheduled',
            'assigned_judge_id' => $judge->id,
        ]);
    }

    public function test_conflicting_manual_schedule_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $judge = User::factory()->create(['role' => 'judge']);
        $caseA = CourtCase::factory()->create(['status' => 'pending']);
        $caseB = CourtCase::factory()->create(['status' => 'pending']);
        $date = now()->addDays(2)->toDateString();

        Hearing::factory()->create([
            'case_id' => $caseA->id,
            'judge_id' => $judge->id,
            'courtroom' => 'Courtroom B',
            'hearing_date' => $date,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'scheduled',
        ]);

        $token = $admin->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/schedule/manual', [
                'case_id' => $caseB->id,
                'judge_id' => $judge->id,
                'courtroom' => 'Courtroom B',
                'hearing_date' => $date,
                'start_time' => '11:30',
                'end_time' => '12:30',
            ]);

        $response->assertStatus(422)->assertJsonStructure(['error', 'conflicts']);
    }
}
