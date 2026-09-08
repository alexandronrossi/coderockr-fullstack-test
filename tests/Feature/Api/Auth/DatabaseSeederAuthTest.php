<?php

namespace Tests\Feature\Api\Auth;

use App\Enums\UserRole;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_admin_and_owner_who_can_log_in(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = $this->postJson('/api/login', [
            'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
            'password' => env('SEED_ADMIN_PASSWORD', 'password'),
        ]);

        $admin->assertOk()
            ->assertJsonPath('user.role', UserRole::Admin->value);

        $owner = $this->postJson('/api/login', [
            'email' => env('SEED_OWNER_EMAIL', 'owner@example.com'),
            'password' => env('SEED_OWNER_PASSWORD', 'password'),
        ]);

        $owner->assertOk()
            ->assertJsonPath('user.role', UserRole::Owner->value);
    }
}
