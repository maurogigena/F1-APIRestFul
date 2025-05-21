<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseDriverRequest;

class StoreDriverRequest extends BaseDriverRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        // Solo admins pueden crear drivers (como dijiste)
        return $this->user()?->is_admin === true;
    }

    /**
     * Reglas de validación para almacenar un nuevo driver.
     */
    public function rules(): array
    {
        return [
            'data' => 'required|array',
            'data.attributes' => 'required|array',
            'data.attributes.number' => 'required|integer|unique:drivers,number',
            'data.attributes.name' => 'required|string|max:255',
            'data.attributes.team' => 'required|string|max:255',
            'data.attributes.age' => 'required|integer',
            'data.attributes.country' => 'required|string',
            'data.attributes.experience' => 'sometimes|string|in:rookie,experienced,legendary',
        ];
    }

    /**
     * Parámetros del cuerpo para documentación (opcional para Scribe).
     */
    public function bodyParameters(): array
    {
        return [
            'data.attributes.name' => [
                'description' => 'Nombre completo del piloto.',
                'example' => 'Max Verstappen',
            ],
            'data.attributes.team' => [
                'description' => 'ID de la escudería del piloto.',
                'example' => 1,
            ],
            'data.attributes.number' => [
                'description' => 'Número del piloto.',
                'example' => 33,
            ],
            'data.attributes.age' => [
                'description' => 'Edad del piloto.',
                'example' => 26,
            ],
            'data.attributes.country' => [
                'description' => 'País del piloto.',
                'example' => 'Netherlands',
            ],
            'data.attributes.experience' => [
                'description' => 'Nivel de experiencia en la F1.',
                'example' => 'experienced',
            ],
        ];
    }
}