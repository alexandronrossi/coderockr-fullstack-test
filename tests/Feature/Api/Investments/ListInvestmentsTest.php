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
        $alice = User::factory()->owner()->create();
        $bob = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $aliceToken = $alice->createToken('api')->plainTextToken;
        $bobToken = $bob->createToken('api')->plainTextToken;
        $adminToken = $admin->createToken('api')->plainTextToken;

        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-10',
        ], [
            'Authorization' => 'Bearer '.$aliceToken,
        ])->assertCreated();
        $this->postJson('/api/investments', [
            'amount' => '3000.00',
            'created_on' => '2025-01-11',
        ], [
            'Authorization' => 'Bearer '.$bobToken,
        ])->assertCreated();

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

    public function test_list_includes_server_computed_total_balance_over_active_portfolio(): void
    {
        $this->travelTo('2025-02-15');

        $owner = User::factory()->owner()->create();
        $other = User::factory()->owner()->create();
        $ownerToken = $owner->createToken('api')->plainTextToken;
        $otherToken = $other->createToken('api')->plainTextToken;

        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$ownerToken,
        ])->assertCreated();
        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$ownerToken,
        ])->assertCreated();

        $withdrawnId = $this->postJson('/api/investments', [
            'amount' => '5000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$ownerToken,
        ])->assertCreated()->json('data.id');

        $this->postJson("/api/investments/{$withdrawnId}/withdraw", [
            'withdrawn_on' => '2025-02-15',
        ], [
            'Authorization' => 'Bearer '.$ownerToken,
        ])->assertOk();

        $this->postJson('/api/investments', [
            'amount' => '9000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$otherToken,
        ])->assertCreated();

        $response = $this->getJson('/api/investments', [
            'Authorization' => 'Bearer '.$ownerToken,
        ]);

        $response->assertOk()
            ->assertJsonPath('summary.total_balance', '2010.40');
    }

    public function test_list_summary_is_stable_across_pages(): void
    {
        $this->travelTo('2025-02-15');

        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        for ($i = 1; $i <= 16; $i++) {
            $this->postJson('/api/investments', [
                'amount' => '1000.00',
                'created_on' => '2025-01-15',
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

        $page1->assertOk()->assertJsonPath('summary.total_balance', '16083.20');
        $page2->assertOk()->assertJsonPath('summary.total_balance', '16083.20');
        $this->assertSame(
            $page1->json('summary.total_balance'),
            $page2->json('summary.total_balance'),
        );
    }

    public function test_empty_list_returns_zero_total_balance(): void
    {
        $owner = User::factory()->owner()->create();
        $token = $owner->createToken('api')->plainTextToken;

        $this->getJson('/api/investments', [
            'Authorization' => 'Bearer '.$token,
        ])
            ->assertOk()
            ->assertJsonPath('summary.total_balance', '0.00')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_admin_summary_includes_all_owners_active_investments(): void
    {
        $this->travelTo('2025-02-15');

        $alice = User::factory()->owner()->create();
        $bob = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $aliceToken = $alice->createToken('api')->plainTextToken;
        $bobToken = $bob->createToken('api')->plainTextToken;
        $adminToken = $admin->createToken('api')->plainTextToken;

        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$aliceToken,
        ])->assertCreated();
        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ], [
            'Authorization' => 'Bearer '.$bobToken,
        ])->assertCreated();

        $this->getJson('/api/investments', [
            'Authorization' => 'Bearer '.$adminToken,
        ])
            ->assertOk()
            ->assertJsonPath('summary.total_balance', '2010.40');
    }
}
