<?php

namespace App\Policies;

use App\Models\Technician;
use App\Models\TechnicianRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RatingPolicy
{
    /**
     * Determine whether the user can create a rating for a technician request.
     */
    public function create(User $user, Technician $technician, TechnicianRequest $technicianRequest): Response
    {
        // Keep role checks centralized through the HasAuthorization helpers on User.
        if (! $user->isUser() && ! $user->isAdmin() && ! $user->isTechnician()) {
            return Response::deny('Forbidden');
        }

        if (! $technicianRequest->isUser($user)) {
            return Response::deny('Forbidden');
        }

        if (! $technicianRequest->isTechnician($technician)) {
            return Response::denyWithStatus(400, 'Technician mismatch');
        }

        if ($technicianRequest->hasRating()) {
            return Response::denyWithStatus(409, 'Rating already exists for this request');
        }

        return Response::allow();
    }
}
