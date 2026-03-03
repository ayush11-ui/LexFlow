<?php

namespace Database\Seeders;

use App\Models\TrackRule;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@lexflow.local'],
            [
                'name' => 'System Admin',
                'password' => 'Admin@12345',
                'role' => 'admin',
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'clerk@lexflow.local'],
            [
                'name' => 'Court Clerk',
                'password' => 'Clerk@12345',
                'role' => 'clerk',
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'judge.civil@lexflow.local'],
            [
                'name' => 'Judge Civil',
                'password' => 'Judge@12345',
                'role' => 'judge',
                'specialization' => 'civil',
            ]
        );

        TrackRule::query()->firstOrCreate(
            ['case_type' => 'traffic'],
            [
                'document_threshold' => 10,
                'duration_threshold' => 1.50,
                'assigned_track' => 'fast',
            ]
        );

        TrackRule::query()->firstOrCreate(
            ['case_type' => 'criminal'],
            [
                'document_threshold' => 50,
                'duration_threshold' => 5.00,
                'assigned_track' => 'complex',
            ]
        );
    }
}
