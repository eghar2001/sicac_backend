<?php

namespace App\Policies;

use App\Models\Technician;
use App\Models\TechnicianRequest;
use App\Models\User;

class TechnicianRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function viewStats(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isUser() || $user->isAdmin() || $user->isTechnician();
    }

    public function viewOwn(User $user): bool
    {
        return $user->isUser() || $user->isAdmin() || $user->isTechnician();
    }

    public function viewUnassigned(User $user): bool
    {
        return $user->isTechnician();
    }

    public function viewMyRequests(User $user): bool
    {
        return $user->isTechnician();
    }

    public function updateStatus(User $user, TechnicianRequest $technicianRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isTechnician()) {
            return false;
        }

        $technician = Technician::where('user_id', $user->id)->first();

        return $technician !== null
            && (int) $technicianRequest->technician_id === (int) $technician->id;
    }

    public function update(User $user, TechnicianRequest $technicianRequest): bool
    {
        return $user->isAdmin();
    }

    public function assignToMyself(User $user, TechnicianRequest $technicianRequest): bool
    {
        return $user->isTechnician();
    }

    public function delete(User $user, TechnicianRequest $technicianRequest): bool
    {
        return $user->isAdmin();
    }
}
