<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_fetch_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'Password@123',
            'role' => 'admin',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password@123',
        ]);

        $login->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ]);

        $token = $login->json('token');
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@example.com');
    }

    public function test_register_requires_admin_role(): void
    {
        $clerk = User::factory()->create(['role' => 'clerk']);
        $token = $clerk->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/register', [
                'name' => 'Judge One',
                'email' => 'judge.one@example.com',
                'password' => 'Password@123',
                'role' => 'judge',
            ])
            ->assertForbidden();
    }
}
