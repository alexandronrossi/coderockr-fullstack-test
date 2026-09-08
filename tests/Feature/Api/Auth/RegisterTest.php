<?php

namespace Tests\Feature\Api\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_owner_issues_token_and_ignores_role(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New Owner',
            'email' => 'new@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'role' => 'admin',
            'user_id' => 999,
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'new@example.com')
            ->assertJsonPath('user.name', 'New Owner')
            ->assertJsonPath('user.role', UserRole::Owner->value)
            ->assertJsonMissingPath('user.password');

        $this->assertNotEmpty($response->json('token'));

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'role' => UserRole::Owner->value,
        ]);

        $user = User::query()->where('email', 'new@example.com')->first();
        $this->assertTrue($user?->isOwner());
        $this->assertFalse($user?->isAdmin());
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->owner()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_oversized_fields_are_rejected(): void
    {
        $this->postJson('/api/register', [
            'name' => str_repeat('n', 256),
            'email' => str_repeat('a', 250).'@example.com',
            'password' => str_repeat('p', 73),
            'password_confirmation' => str_repeat('p', 73),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_short_password_is_rejected(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Short',
            'email' => 'short@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Mismatch',
            'email' => 'mismatch@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password2',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }
}
