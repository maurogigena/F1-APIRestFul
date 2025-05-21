<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseUserRequest;

class StoreUserRequest extends BaseUserRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $authUser = $this->user();

        // Solo los admins pueden crear usuarios
        return $authUser && $authUser->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'data.attributes.name' => 'required|string',
            'data.attributes.email' => 'required|email|unique:users,email',
            'data.attributes.isAdmin' => 'required|boolean',
            'data.attributes.password' => 'required|string|min:5'
        ];
    }
}