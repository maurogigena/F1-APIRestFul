<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\BaseCircuitRequest;

class StoreCircuitRequest extends BaseCircuitRequest
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
            'data.attributes.name' => 'required|string|max:255',
            'data.attributes.country' => 'required|string|max:255',
            'data.attributes.city' => 'required|string|max:255',
            'data.attributes.recordDriver' => 'required|string|max:255',
        ];
    }

    /**
     * Parámetros del cuerpo para documentación (opcional para Scribe).
     */
    public function bodyParameters(): array
    {
        return [
            'data.attributes.name' => [
                'description' => 'Nombre del circuito.',
                'example' => 'Albert Park',
            ],
            'data.attributes.country' => [
                'description' => 'País del circuito.',
                'example' => 'Australia',
            ],
            'data.attributes.city' => [
                'description' => 'Ciudad del circuito.',
                'example' => 'Melbourne',
            ],
            'data.attributes.recordDriver' => [
                'description' => 'Quien tiene el record absoluto en ese circuito.',
                'example' => 'Lando Norris',
            ],
        ];
    }
}