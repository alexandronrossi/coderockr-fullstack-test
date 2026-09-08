<?php

namespace Tests\Feature\Api\Investments;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListInvestmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_lists_only_own_investments(): void
    {
        $alice = User::factory()->owner()->create();
        $bob = User::factory()->owner()->create();
        $aliceToken = $alice->createToken('api')->plainTextToken;
        $bobToken = $bob->createToken('api')->plainTextToken;

        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-10',
        ], [
            'Authorization' => 'Bearer '.$aliceToken,
        ]);
        $this->postJson('/api/investments', [
            'amount' => '2000.00',
            'created_on' => '2025-01-11',
        ], [
            'Authorization' => 'Bearer '.$bobToken,
        ]);

        $response = $this->getJson('/api/investments?user_id='.$bob->id, [
            'Authorization' => 'Bearer '.$aliceToken,
        ]);

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.owner.id', $alice->id);

        $this->assertSame('1000.00', $response->json('data.0.amount'));
        $this->assertNotSame($bob->id, $response->json('data.0.owner.id'));
    }

    public function test_admin_lists_investments_from_all_owners(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $ownerToken = $owner->createToken('api')->plainTextToken;
        $adminToken = $admin->createToken('api')->plainTextToken;

        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-10',
        ], [
            'Authorization' => 'Bearer '.$ownerToken,
        ]);
        $this->postJson('/api/investments', [
            'amount' => '3000.00',
            'created_on' => '2025-01-11',
        ], [
            'Authorization' => 'Bearer '.$adminToken,
        ]);

        $this->getJson('/api/investments', [
            'Authorization' => 'Bearer '.$adminToken,
        ])
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_list_is_paginated(): void
    {
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        for ($i = 1; $i <= 16; $i++) {
            $this->postJson('/api/investments', [
                'amount' => '1000.00',
                'created_on' => '2025-01-01',
            ], [
                'Authorization' => 'Bearer '.$token,
            ])->assertCreated();
        }

        $page1 = $this->getJson('/api/investments?per_page=15&page=1', [
            'Authorization' => 'Bearer '.$token,
        ]);
        $page2 = $this->getJson('/api/investments?per_page=15&page=2', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $page1->assertOk()
            ->assertJsonPath('meta.total', 16)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.current_page', 1);
        $this->assertCount(15, $page1->json('data'));

        $page2->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 16);
        $this->assertCount(1, $page2->json('data'));
        $this->assertNotSame($page1->json('data.0.id'), $page2->json('data.0.id'));
    }

    public function test_per_page_above_maximum_is_rejected(): void
    {
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $this->getJson('/api/investments?per_page=101', [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnprocessable();
    }
}
