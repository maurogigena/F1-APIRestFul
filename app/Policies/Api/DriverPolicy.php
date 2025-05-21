<?php

namespace App\Policies\Api;

use App\Models\User;
use App\Permissions\Api\Abilities;

class DriverPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the user can view one driver.
     */
    public function view(User $user)
    {
        return $user->tokenCan(Abilities::ViewUser);
    }

    /**
     * Determine if the user can create a new driver.
     */
    public function store(User $user)
    {
        return $user->is_admin && $user->tokenCan(Abilities::CreateDriver);
    }

    /**
     * Determine if the user can update a driver.
     */
    public function update(User $user)
    {
        return $user->is_admin && $user->tokenCan(Abilities::UpdateDriver);
    }

    /**
     * Determine if the user can replace a driver.
     */
    public function replace(User $user) : bool
    {
        return $user->is_admin && $user->tokenCan(Abilities::ReplaceDriver);
    }

    /**
     * Determine if the user can delete a driver.
     */
    public function delete(User $user)
    {
        return $user->is_admin && $user->tokenCan(Abilities::DeleteDriver);
    }
}