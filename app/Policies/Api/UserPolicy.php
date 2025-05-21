<?php

namespace App\Policies\Api;

use App\Models\User;
use App\Permissions\Api\Abilities;

class UserPolicy
{
    /**
     * Determine whether the user can view any users (index).
     */
    public function viewAny(User $user)
    {
        return $user->tokenCan(Abilities::ViewAnyUser) && $user->is_admin;
    }

    /**
     * Determine whether the user can view a specific user (show).
     */
   public function view(User $user, User $model)
    {
        return $user->tokenCan(Abilities::ViewUser) &&
            ($user->is_admin || $user->id === $model->id);
    }
    

    /**
     * Determine whether the user can replace the given user.
     */
    public function replace(User $user, User $model)
    {
        return $user->tokenCan(Abilities::ReplaceUser) &&
            ($user->is_admin || $user->id === $model->id);
    }

    /**
     * Determine whether the user can create a new user.
     * Only admins can do this.
     */
    public function store(User $user)
    {
        return $user->tokenCan(Abilities::CreateUser) && $user->is_admin;
    }

    /**
     * Determine whether the user can update the given user.
     */
    public function update(User $user, User $model)
    {
        return $user->tokenCan(Abilities::UpdateUser) &&
            ($user->is_admin || $user->id === $model->id);
    }

    /**
     * Determine whether the user can delete the given user.
     */
    public function delete(User $user, User $model)
    {
        return $user->tokenCan(Abilities::DeleteUser) &&
            ($user->is_admin || $user->id === $model->id);
    }
}
