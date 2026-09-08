<?php

namespace Tests\Feature\Api\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_credentials_issue_token_and_user_without_password(): void
    {
        $user = User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'owner@example.com')
            ->assertJsonPath('user.role', UserRole::Owner->value)
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('password')
            ->assertJsonMissingPath('user.remember_token');

        $this->assertNotEmpty($response->json('token'));
        $this->assertIsString($response->json('token'));
    }

    public function test_login_ignores_client_supplied_role_and_user_id(): void
    {
        $user = User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'password',
            'role' => 'admin',
            'is_admin' => true,
            'user_id' => $user->id + 99,
        ]);

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.role', UserRole::Owner->value);

        $this->assertSame(UserRole::Owner, $user->fresh()->role);
    }

    public function test_unknown_email_returns_generic_unauthorized_message(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertUnauthorized()
            ->assertExactJson(['message' => 'Invalid credentials.']);
    }

    public function test_wrong_password_returns_the_same_generic_message(): void
    {
        User::factory()->owner()->create([
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $unknown = $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ]);

        $wrong = $this->postJson('/api/login', [
            'email' => 'owner@example.com',
            'password' => 'not-the-password',
        ]);

        $this->assertSame($unknown->status(), $wrong->status());
        $this->assertSame($unknown->json(), $wrong->json());
        $wrong->assertUnauthorized()
            ->assertExactJson(['message' => 'Invalid credentials.']);
    }

    public function test_empty_body_returns_unprocessable(): void
    {
        $this->postJson('/api/login', [])->assertUnprocessable();
    }

    public function test_malformed_email_returns_unprocessable(): void
    {
        $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ])->assertUnprocessable();
    }
}
