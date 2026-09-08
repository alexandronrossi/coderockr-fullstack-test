<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginFieldLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_oversized_email_is_rejected(): void
    {
        $this->postJson('/api/login', [
            'email' => str_repeat('a', 250).'@example.com',
            'password' => 'password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_oversized_password_is_rejected(): void
    {
        User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => str_repeat('x', 73),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
