<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseDriverRequest;

class ReplaceDriverRequest extends BaseDriverRequest
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
            'data.attributes.team' => 'required|string|max:255',
            'data.attributes.age' => 'required|integer',
            'data.attributes.country' => 'required|string',
            'data.attributes.experience' => 'required|string|in:rookie,experienced,legendary',
        ];
    }
}