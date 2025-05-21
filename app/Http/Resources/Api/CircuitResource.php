<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CircuitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAdmin = $request->user() && $request->user()->is_admin;

        $attributes = [];

        if ($isAdmin) {
            $attributes['id'] = $this->id;
        }

        $attributes['name'] = $this->name;
        $attributes['country'] = $this->country;
        $attributes['city'] = $this->city;
        $attributes['trackRecord'] = $this->record_driver_id;

        if ($isAdmin) {
            $attributes['createdAt'] = $this->created_at;
            $attributes['updatedAt'] = $this->updated_at;
        }

        return [
            'type' => 'circuit',
            'attributes' => $attributes,
            'links' => [
                'self' => route('circuits.show', ['circuit' => $this->id]),
            ]
        ];
    }
}
