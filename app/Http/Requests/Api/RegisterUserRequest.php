<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ya controlás permisos en el controller
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'is_admin' => 'boolean'
        ];

        if (!$this->user() || !$this->user()->is_admin) {
            // si no es admin, pido confirmación
            $rules['password'] .= '|confirmed';
        }

        return $rules;
    }
}
