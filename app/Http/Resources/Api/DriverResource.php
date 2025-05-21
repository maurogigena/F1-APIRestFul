<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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

        $attributes['number'] = $this->number;
        $attributes['name'] = $this->name;
        $attributes['team'] = $this->team ? $this->team->name : null;
        $attributes['age'] = $this->age;
        $attributes['country'] = $this->country;
        $attributes['experience'] = $this->experience;

        if ($isAdmin) {
            $attributes['createdAt'] = $this->created_at;
            $attributes['updatedAt'] = $this->updated_at;
        }

        return [
            'type' => 'driver',
            'attributes' => $attributes,
            'links' => [
                'self' => route('drivers.show', ['driver' => $this->id]),
            ]
        ];
    }
}
