<?php

namespace Database\Factories;

use App\Models\Investment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Investment>
 */
class InvestmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount_cents' => 100_000,
            'created_on' => now()->toDateString(),
            'withdrawn_on' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Investment $investment): void {
            if ($investment->user_id !== null) {
                return;
            }

            $investment->user()->associate(User::factory()->create());
        });
    }

    public function forUser(User $user): static
    {
        return $this->afterMaking(function (Investment $investment) use ($user): void {
            $investment->user()->associate($user);
        });
    }

    public function withdrawn(): static
    {
        return $this->state(fn (array $attributes) => [
            'withdrawn_on' => $attributes['created_on'] ?? now()->toDateString(),
        ]);
    }
}
