<?php

namespace Tests\Feature\Api\Investments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreInvestmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_creates_an_active_investment_for_themselves(): void
    {
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $response = $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.amount', '1000.00')
            ->assertJsonPath('data.created_on', '2025-01-15')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.owner.id', $owner->id)
            ->assertJsonPath('data.owner.email', $owner->email)
            ->assertJsonPath('data.expected_balance', '1000.00')
            ->assertJsonPath('data.gain', '0.00')
            ->assertJsonMissingPath('data.owner.password');

        $this->assertDatabaseHas('investments', [
            'user_id' => $owner->id,
            'amount_cents' => 100_000,
        ]);
    }

    public function test_zero_amount_is_rejected(): void
    {
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $this->postJson('/api/investments', [
            'amount' => '0.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('investments', 0);
    }

    public function test_negative_amount_is_rejected(): void
    {
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $this->postJson('/api/investments', [
            'amount' => '-1.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('investments', 0);
    }

    public function test_future_created_on_is_rejected(): void
    {
        $this->travelTo('2025-01-15');
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-16',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('investments', 0);
    }

    public function test_admin_cannot_create_an_investment(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('api')->plainTextToken;

        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertForbidden();

        $this->assertDatabaseCount('investments', 0);
    }
}
