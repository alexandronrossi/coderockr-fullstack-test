<?php

namespace Tests\Feature\Api\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivilegeEscalationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_profile_ignore_role_elevation_fields(): void
    {
        $user = User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'password',
            'role' => UserRole::Admin->value,
            'is_admin' => true,
        ]);

        $login->assertOk()
            ->assertJsonPath('user.role', UserRole::Owner->value);

        $this->getJson('/api/user?role=admin&is_admin=1', [
            'Authorization' => 'Bearer '.$login->json('token'),
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', UserRole::Owner->value);

        $this->assertSame(UserRole::Owner, $user->fresh()->role);
    }

    public function test_factory_admin_and_owner_states(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->owner()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isOwner());
        $this->assertTrue($owner->isOwner());
        $this->assertFalse($owner->isAdmin());
    }
}
