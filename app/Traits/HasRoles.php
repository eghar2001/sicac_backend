<?php

namespace App\Traits;

use App\Models\User;

trait HasRoles
{
    /**
     * Check if the user has any of the given roles.
     *
     * @param array<string> $roles
     * @return bool
     */
    public function roles(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a technician.
     *
     * @return bool
     */
    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    /**
     * Check if the user is a standard user.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }


}
