<?php

namespace App\Models;

use App\Domain\Investment\Money;
use Carbon\CarbonImmutable;
use Database\Factories\InvestmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['amount_cents', 'created_on', 'withdrawn_on'])]
class Investment extends Model
{
    /** @use HasFactory<InvestmentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'created_on' => 'date',
            'withdrawn_on' => 'date',
        ];
    }

    /**
     * @param  Builder<Investment>  $query
     * @return Builder<Investment>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function principal(): Money
    {
        return Money::fromCents($this->amount_cents);
    }

    public function createdOn(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->created_on)->startOfDay();
    }

    public function withdrawnOn(): ?CarbonImmutable
    {
        if ($this->withdrawn_on === null) {
            return null;
        }

        return CarbonImmutable::parse($this->withdrawn_on)->startOfDay();
    }

    public function status(): string
    {
        return $this->withdrawn_on === null ? 'active' : 'withdrawn';
    }
}
