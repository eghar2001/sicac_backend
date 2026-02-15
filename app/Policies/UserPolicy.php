<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function createAdmin(?User $user): Response
    {
        $adminExists = User::where('role', 'admin')->exists();

        if (! $adminExists) {
            return Response::allow();
        }

        if (! $user) {
            return Response::denyWithStatus(401, 'Unauthenticated.');
        }

        if (! $user->isAdmin()) {
            return Response::deny('Forbidden.');
        }

        return Response::allow();
    }
}
