<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->owner()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->assertSame(1, $user->tokens()->count());

        $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ])->assertNoContent();

        $this->assertSame(0, $user->fresh()->tokens()->count());

        $this->getJson('/api/user', [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnauthorized();
    }

    public function test_logout_without_token_is_unauthorized(): void
    {
        $this->postJson('/api/logout')->assertUnauthorized();
    }
}
