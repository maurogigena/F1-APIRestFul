<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Team;

class BaseDriverRequest extends FormRequest
{
    public function mappedAttributes(array $otherAttributes = []) 
    {
        $attributeMap = array_merge([
            'data.attributes.id' => 'id',
            'data.attributes.number' => 'number',
            'data.attributes.name' => 'name',
            'data.attributes.team' => 'team_id',
            'data.attributes.age' => 'age',
            'data.attributes.country' => 'country',
            'data.attributes.experience' => 'experience',
            'data.attributes.createdAt' => 'created_at',
            'data.attributes.updatedAt' => 'updated_at'
        ], $otherAttributes);

        $attributesToUpdate = [];
        foreach ($attributeMap as $key => $attribute) {
            if ($this->has($key)) {
                $attributesToUpdate[$attribute] = $this->input($key);
            }
        }

        // Si el campo team contiene un nombre de equipo en lugar de un ID,
        // buscar el ID correspondiente
        if (isset($attributesToUpdate['team_id']) && is_string($attributesToUpdate['team_id'])) {
            $team = Team::where('name', $attributesToUpdate['team_id'])->first();
            if ($team) {
                $attributesToUpdate['team_id'] = $team->id;
            }
        }

        // Evitar que el ID sea reemplazado
        unset($attributesToUpdate['id']);

        return $attributesToUpdate;
    }
}