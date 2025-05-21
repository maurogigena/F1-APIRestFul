<?php

namespace App\Permissions\Api;

use App\Models\User;

final class Abilities 
{
    public const CreateDriver = 'driver:create';
    public const UpdateDriver = 'driver:update';
    public const ReplaceDriver = 'driver:replace';
    public const DeleteDriver = 'driver:delete';
    public const ViewDriver = 'driver:view';

    public const CreateCircuit = 'circuit:create';
    public const UpdateCircuit = 'circuit:update';
    public const ReplaceCircuit = 'circuit:replace';
    public const DeleteCircuit = 'circuit:delete';

    public const ViewUser = 'user:view';
    public const ViewAnyUser = 'user:any:view';    

    public const CreateUser = 'user:create';
    public const UpdateUser = 'user:update';
    public const ReplaceUser = 'user:replace';
    public const DeleteUser = 'user:delete';

    public static function getAbilities(User $user)
    { // don't assign '*'
        if ($user->is_admin) {
            return [
                self::CreateDriver,
                self::UpdateDriver,
                self::ReplaceDriver,
                self::DeleteDriver,
                self::ViewDriver,
                self::CreateCircuit,
                self::UpdateCircuit,
                self::ReplaceCircuit,
                self::DeleteCircuit,
                self::ViewUser,
                self::ViewAnyUser,
                self::CreateUser,
                self::UpdateUser,
                self::ReplaceUser,
                self::DeleteUser,
            ];
        } else {
            return [
                self::ViewDriver,
                self::ViewUser,
                self::UpdateUser,
                self::ReplaceUser,
                self::DeleteUser,
            ];    
        }
    }
}