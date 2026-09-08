<?php

namespace Tests\Feature\Api\Investments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentMassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_ignores_owner_status_and_valuation_fields(): void
    {
        $owner = User::factory()->owner()->create();
        $other = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $response = $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
            'user_id' => $other->id,
            'status' => 'withdrawn',
            'expected_balance' => '9999.00',
            'tax' => '1.00',
            'withdrawn_on' => '2025-02-01',
            'role' => 'admin',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.owner.id', $owner->id)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.expected_balance', '1000.00')
            ->assertJsonPath('data.withdrawn_on', null);

        $this->assertDatabaseHas('investments', [
            'user_id' => $owner->id,
            'withdrawn_on' => null,
        ]);
        $this->assertDatabaseMissing('investments', [
            'user_id' => $other->id,
        ]);
    }

    public function test_withdraw_ignores_client_supplied_tax_and_owner(): void
    {
        $this->travelTo('2025-02-15');
        $owner = User::factory()->owner()->create();
        $other = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $create = $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);
        $id = $create->json('data.id');

        $response = $this->postJson('/api/investments/'.$id.'/withdraw', [
            'withdrawn_on' => '2025-02-15',
            'tax' => '0.00',
            'net' => '0.00',
            'rate' => '0.15',
            'expected_balance' => '1.00',
            'user_id' => $other->id,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'withdrawn')
            ->assertJsonPath('data.owner.id', $owner->id);

        $this->assertNotSame('0.00', $response->json('data.tax'));
        $this->assertNotSame('1.00', $response->json('data.expected_balance'));
    }
}
