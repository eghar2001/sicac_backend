<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\User;

class ClaimPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewOwn(User $user): bool
    {
        return $user->isUser() || $user->isAdmin() || $user->isTechnician();
    }

    public function create(User $user): bool
    {
        return $user->isUser() || $user->isAdmin() || $user->isTechnician();
    }

    public function update(User $user, Claim $claim): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Claim $claim): bool
    {
        return $user->isAdmin();
    }

    public function answer(User $user, Claim $claim): bool
    {
        return $user->isAdmin();
    }
}
