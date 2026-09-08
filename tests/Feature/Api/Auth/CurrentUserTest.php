<?php

namespace Tests\Feature\Api\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrentUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_profile(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_token_owner_sees_own_profile(): void
    {
        $user = User::factory()->owner()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->getJson('/api/user', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.role', UserRole::Owner->value)
            ->assertJsonMissingPath('data.password');
    }

    public function test_query_user_id_does_not_change_whose_profile_is_returned(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $response = $this->getJson('/api/user?user_id='.$admin->id, [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $owner->id);
        $this->assertNotSame($admin->id, $response->json('data.id'));
    }
}
