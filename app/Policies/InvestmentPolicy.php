<?php

namespace App\Policies;

use App\Models\Investment;
use App\Models\User;

class InvestmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Investment $investment): bool
    {
        return $this->ownsOrAdministers($user, $investment);
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function withdraw(User $user, Investment $investment): bool
    {
        return $this->ownsOrAdministers($user, $investment);
    }

    private function ownsOrAdministers(User $user, Investment $investment): bool
    {
        return $user->isAdmin() || $investment->user_id === $user->id;
    }
}
