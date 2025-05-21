<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseDriverRequest;

class UpdateDriverRequest extends BaseDriverRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Reglas de validación para actualizar parcialmente un driver.
     */
    public function rules(): array
    {
        return [
            'data.attributes.id' => 'sometimes|integer',
            'data.attributes.name' => 'sometimes|string|max:255',
            'data.attributes.team' => 'sometimes|string|max:255',
            'data.attributes.age' => 'sometimes|integer|min:18|max:70',
            'data.attributes.country' => 'sometimes|string|min:18|max:255',
            'data.attributes.experience' => 'sometimes|string|in:rookie,experienced,legendary',
        ];
    }
}
