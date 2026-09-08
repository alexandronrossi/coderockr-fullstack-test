<?php

namespace Tests\Feature\Api\Investments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowInvestmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_compound_balance_after_one_civil_month(): void
    {
        $this->travelTo('2025-02-15');
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $id = $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->json('data.id');

        $this->getJson('/api/investments/'.$id, [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('data.amount', '1000.00')
            ->assertJsonPath('data.expected_balance', '1005.20')
            ->assertJsonPath('data.gain', '5.20')
            ->assertJsonPath('data.complete_months', 1);
    }

    public function test_unknown_id_returns_not_found(): void
    {
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $this->getJson('/api/investments/999999', [
            'Authorization' => 'Bearer '.$token,
        ])->assertNotFound();
    }

    public function test_withdrawn_investment_freezes_gains_on_later_show(): void
    {
        $this->travelTo('2025-02-15');
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $id = $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->json('data.id');

        $withdrawn = $this->postJson('/api/investments/'.$id.'/withdraw', [
            'withdrawn_on' => '2025-02-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);
        $withdrawn->assertOk();
        $frozen = $withdrawn->json('data.expected_balance');

        $this->travelTo('2025-06-15');

        $this->getJson('/api/investments/'.$id, [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'withdrawn')
            ->assertJsonPath('data.expected_balance', $frozen)
            ->assertJsonPath('data.withdrawn_on', '2025-02-15');
    }
}
