<?php

namespace App\Policies\Api;

use App\Models\User;
use App\Permissions\Api\Abilities;

class CircuitPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can create a circuit.
     */
    public function store(User $user): bool
    {
        return $user->is_admin && $user->tokenCan(Abilities::CreateCircuit);
    }

    /**
     * Determine whether the user can update a circuit.
     */
    public function update(User $user): bool
    {
        return $user->is_admin && $user->tokenCan(Abilities::UpdateCircuit);
    }

    /**
     * Determine whether the user can replace a circuit.
     */
    public function replace(User $user): bool
    {
        return $user->is_admin && $user->tokenCan(Abilities::ReplaceCircuit);
    }

    /**
     * Determine whether the user can delete a circuit.
     */
    public function delete(User $user): bool
    {
        return $user->is_admin && $user->tokenCan(Abilities::DeleteCircuit);
    }
}
