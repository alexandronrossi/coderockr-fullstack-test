<?php

namespace Tests\Feature\Api\Investments;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentUnauthenticatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_investments(): void
    {
        $this->postJson('/api/investments', [
            'amount' => '1000.00',
            'created_on' => '2025-01-15',
        ])->assertUnauthorized();
    }

    public function test_guest_cannot_list_investments(): void
    {
        $this->getJson('/api/investments')->assertUnauthorized();
    }

    public function test_guest_cannot_show_an_investment(): void
    {
        $this->getJson('/api/investments/1')->assertUnauthorized();
    }

    public function test_guest_cannot_withdraw_an_investment(): void
    {
        $this->postJson('/api/investments/1/withdraw', [
            'withdrawn_on' => '2025-02-15',
        ])->assertUnauthorized();
    }
}
