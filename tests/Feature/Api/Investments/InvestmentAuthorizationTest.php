<?php

namespace Tests\Feature\Api\Investments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_cannot_show_another_owners_investment(): void
    {
        $alice = User::factory()->owner()->create();
        $bob = User::factory()->owner()->create();
        $aliceToken = $alice->createToken('api')->plainTextToken;
        $bobToken = $bob->createToken('api')->plainTextToken;

        $id = $this->postJson('/api/investments', [
            'amount' => '2500.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$aliceToken,
        ])->json('data.id');

        $response = $this->getJson('/api/investments/'.$id, [
            'Authorization' => 'Bearer '.$bobToken,
        ]);

        $response->assertNotFound();
        $this->assertStringNotContainsString('2500.00', (string) $response->getContent());
    }

    public function test_admin_can_show_an_owners_investment(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $ownerToken = $owner->createToken('api')->plainTextToken;
        $adminToken = $admin->createToken('api')->plainTextToken;

        $id = $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$ownerToken,
        ])->json('data.id');

        $this->getJson('/api/investments/'.$id, [
            'Authorization' => 'Bearer '.$adminToken,
        ])
            ->assertOk()
            ->assertJsonPath('data.amount', '1000.00')
            ->assertJsonPath('data.owner.id', $owner->id);
    }

    public function test_owner_cannot_withdraw_another_owners_investment(): void
    {
        $this->travelTo('2025-02-15');
        $alice = User::factory()->owner()->create();
        $bob = User::factory()->owner()->create();
        $aliceToken = $alice->createToken('api')->plainTextToken;
        $bobToken = $bob->createToken('api')->plainTextToken;

        $id = $this->postJson('/api/investments', [
            'amount' => '2500.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$aliceToken,
        ])->json('data.id');

        $response = $this->postJson('/api/investments/'.$id.'/withdraw', [
            'withdrawn_on' => '2025-02-15',
        ], [
            'Authorization' => 'Bearer '.$bobToken,
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('investments', [
            'id' => $id,
            'withdrawn_on' => null,
        ]);
    }

    public function test_admin_can_withdraw_an_owners_investment_without_changing_owner(): void
    {
        $this->travelTo('2025-02-15');
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $ownerToken = $owner->createToken('api')->plainTextToken;
        $adminToken = $admin->createToken('api')->plainTextToken;

        $id = $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$ownerToken,
        ])->json('data.id');

        $this->postJson('/api/investments/'.$id.'/withdraw', [
            'withdrawn_on' => '2025-02-15',
        ], [
            'Authorization' => 'Bearer '.$adminToken,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'withdrawn')
            ->assertJsonPath('data.owner.id', $owner->id);
    }
}
