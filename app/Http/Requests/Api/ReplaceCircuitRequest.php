<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseCircuitRequest;

class ReplaceCircuitRequest extends BaseCircuitRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Reglas de validación para reemplazar completamente un driver.
     */
    public function rules(): array
    {
        return [
            'data.attributes.name' => 'required|string|max:255',
            'data.attributes.country' => 'required|string|max:255',
            'data.attributes.city' => 'required|string|max:255',
            'data.attributes.recordDriver' => 'required|string|max:255'
        ];
    }
}