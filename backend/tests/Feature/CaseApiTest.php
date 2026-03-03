<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_clerk_can_create_case_and_track_is_predicted(): void
    {
        $clerk = User::factory()->create(['role' => 'clerk']);
        $token = $clerk->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/cases', [
                'title' => 'Urgent Injunction',
                'description' => 'Urgent injunction filing',
                'case_type' => 'civil',
                'urgency_level' => 5,
                'estimated_duration' => 1.5,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.track', 'fast');

        $this->assertDatabaseHas('cases', [
            'title' => 'Urgent Injunction',
            'track' => 'fast',
            'status' => 'pending',
        ]);
    }

    public function test_judge_can_view_only_assigned_cases(): void
    {
        $judge = User::factory()->create(['role' => 'judge']);
        $otherJudge = User::factory()->create(['role' => 'judge']);

        CourtCase::factory()->create(['assigned_judge_id' => $judge->id, 'status' => 'scheduled']);
        CourtCase::factory()->create(['assigned_judge_id' => $otherJudge->id, 'status' => 'scheduled']);

        $token = $judge->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/cases');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
}
