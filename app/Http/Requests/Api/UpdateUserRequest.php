<?php

namespace App\Http\Requests\Api;

class UpdateUserRequest extends BaseUserRequest
{
    /**
     * Determina si el usuario tiene autorización para hacer la solicitud.
     */
    public function authorize(): bool
    {
        $authUser = $this->user(); // Usuario autenticado

        // Si es admin, puede modificar a cualquiera
        if ($authUser->is_admin) {
            return true;
        }

        // Si no es admin, solo puede modificar su propio usuario
        return $authUser->id == ($this->route('user')->id ?? null);
    }

    /**
     * Reglas de validación para actualizar un usuario.
     */
    public function rules(): array
    {
        $rules = [
            'data.attributes.name' => 'sometimes|string',
            'data.attributes.email' => 'sometimes|email',
            'data.attributes.password' => 'sometimes|string',
        ];

        // Solo permitir modificar el atributo isAdmin si el usuario autenticado es admin
        if ($this->user()?->is_admin) {
            $rules['data.attributes.isAdmin'] = 'sometimes|boolean';
        }

        return $rules;
    }
}
