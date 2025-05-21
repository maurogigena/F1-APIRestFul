<?php

namespace App\Http\Controllers\Api;

use App\Http\Filters\Api\UserFilter;
use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Http\Requests\Api\ReplaceUserRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Policies\Api\UserPolicy;
use Illuminate\Support\Facades\Auth;

class UserController extends ApiController
{
    protected $policyClass = UserPolicy::class;
    /**
     * Display a listing of the users (admin only).
     */
    
    public function index(UserFilter $filter)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();
        $filtered = $filter->apply($query);

        return $this->ok('Users', UserResource::collection($filtered->paginate()));
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('store', User::class);

        $attributes = $request->mappedAttributes();

        // Solo managers pueden crear otros admins
        if (!Auth::user()?->is_admin) {
            $attributes['is_admin'] = false;
        }

        return new UserResource(User::create($attributes));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $user->update($request->mappedAttributes());

        return (new UserResource($user))
            ->additional(['message' => 'User Updated Successfully']);
    }

    /**
     * Replace the specified user.
     */
    public function replace(ReplaceUserRequest $request, User $user)
    {
        $this->authorize('replace', $user);

        $user->update($request->mappedAttributes());

        return (new UserResource($user))
            ->additional(['message' => 'User Replaced Successfully']);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return $this->noContent('User Deleted Successfully');
    }
}