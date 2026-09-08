<?php

namespace Tests\Feature\Api\Investments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawInvestmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_withdraws_and_server_computes_tax(): void
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

        $response = $this->postJson('/api/investments/'.$id.'/withdraw', [
            'withdrawn_on' => '2025-02-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'withdrawn')
            ->assertJsonPath('data.expected_balance', '1005.20')
            ->assertJsonPath('data.gain', '5.20')
            ->assertJsonPath('data.withdrawn_on', '2025-02-15');

        $this->assertSame('1.17', $response->json('data.tax'));
        $this->assertSame('1004.03', $response->json('data.net'));
    }

    public function test_second_withdraw_is_rejected(): void
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

        $this->postJson('/api/investments/'.$id.'/withdraw', [
            'withdrawn_on' => '2025-02-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk();

        $this->postJson('/api/investments/'.$id.'/withdraw', [
            'withdrawn_on' => '2025-02-15',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnprocessable();
    }

    public function test_withdraw_before_creation_is_rejected(): void
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

        $this->postJson('/api/investments/'.$id.'/withdraw', [
            'withdrawn_on' => '2025-01-14',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnprocessable();

        $this->assertDatabaseHas('investments', [
            'id' => $id,
            'withdrawn_on' => null,
        ]);
    }

    public function test_future_withdraw_date_is_rejected(): void
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

        $this->postJson('/api/investments/'.$id.'/withdraw', [
            'withdrawn_on' => '2025-02-16',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnprocessable();
    }
}
