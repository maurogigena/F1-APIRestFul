<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseUserRequest;

class ReplaceUserRequest extends BaseUserRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $authUser = $this->user();

        // Si es admin, puede reemplazar a cualquiera
        if ($authUser->is_admin) {
            return true;
        }

        // Si no es admin y quiere cambiar el campo 'isAdmin', se rechaza
        if (!$authUser->is_admin && $this->has('data.attributes.isAdmin')) {
            return false;
        }

        // Si no es admin, solo puede modificar su propio usuario
        return $authUser->id == ($this->route('user')->id ?? null);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $authUser = $this->user();
        
        return [
            'data.attributes.name' => 'required|string',
            'data.attributes.email' => 'required|email',
            'data.attributes.isAdmin' => $authUser->is_admin ? 'required|boolean' : 'prohibited',
            'data.attributes.password' => 'required|string'
        ];
    }
}