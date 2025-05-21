<?php

namespace App\Http\Filters\Api;

use App\Http\Filters\Api\QueryFilter;

class CircuitFilter extends QueryFilter
{
    protected $sortable = [
        'name',
        'country',
        'city',
        'recordDriver',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function name($value)
    {
        return $this->builder->where('name', 'like', str_replace('*', '%', $value));
    }
    
    public function city($value)
    {
        return $this->builder->where('city', 'like', str_replace('*', '%', $value));
    }

    public function country($value)
    {
        return $this->builder->where('country', $value);
    }

    public function recordDriver($value)
    {
        return $this->builder->where('record_driver_id', 'like', str_replace('*', '%', $value));
    }

    public function createdAt($value)
    {
        $dates = explode(',', $value);
        return count($dates) > 1
            ? $this->builder->whereBetween('created_at', $dates)
            : $this->builder->whereDate('created_at', $value);
    }

    public function updatedAt($value)
    {
        $dates = explode(',', $value);
        return count($dates) > 1
            ? $this->builder->whereBetween('updated_at', $dates)
            : $this->builder->whereDate('updated_at', $value);
    }
}