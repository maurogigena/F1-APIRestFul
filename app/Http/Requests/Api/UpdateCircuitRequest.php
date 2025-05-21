<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseCircuitRequest;

class UpdateCircuitRequest extends BaseCircuitRequest
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
            'data.attributes.name' => 'sometimes|string|max:255',
            'data.attributes.country' => 'sometimes|string|max:255',
            'data.attributes.city' => 'sometimes|string|max:255',
            'data.attributes.recordDriver' => 'sometimes|string|max:255',
        ];
    }
}
