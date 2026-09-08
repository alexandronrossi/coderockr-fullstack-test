<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login');
    }

    public function test_sixth_failed_login_is_throttled(): void
    {
        User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $payload = [
            'email' => 'owner@example.com',
            'password' => 'wrong',
        ];

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/login', $payload)->assertUnauthorized();
        }

        $this->postJson('/api/login', $payload)->assertStatus(429);
    }
}
