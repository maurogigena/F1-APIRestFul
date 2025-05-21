<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BaseCircuitRequest extends FormRequest
{
    public function mappedAttributes(array $otherAttributes = []) 
    {
        $attributeMap = array_merge ([
            'data.attributes.name' => 'name',
            'data.attributes.country' => 'country',
            'data.attributes.city' => 'city',
            'data.attributes.recordDriver' => 'record_driver_id',
            'data.attributes.createdAt' => 'created_at',
            'data.attributes.updatedAt' => 'updated_at'
        ], $otherAttributes);

        $attributesToUpdate = [];
        foreach ($attributeMap as $key => $attribute) {
            if ($this->has($key)) {
                $attributesToUpdate[$attribute] = $this->input($key);
            }
        }

        return $attributesToUpdate;
    }
}
