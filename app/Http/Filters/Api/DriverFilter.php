<?php

namespace App\Http\Filters\Api;

use App\Http\Filters\Api\QueryFilter;

class DriverFilter extends QueryFilter
{
    protected $sortable = [
        'id',
        'name',
        'team_id',
        'age',
        'country',
        'experience',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function id($value)
    {
        return $this->builder->where('id', $value);
    }

    public function age($value)
    {
        return $this->builder->where('age', $value);
    }

    public function name($value)
    {
        return $this->builder->where('name', 'like', str_replace('*', '%', $value));
    }

    public function team($value)
    {
        return $this->builder->where('team_id', 'like', str_replace('*', '%', $value));
    }

    public function country($value)
    {
        return $this->builder->where('country', 'like', str_replace('*', '%', $value));
    }

    public function experience($value)
    {
        $searchTerm = str_replace('*', '%', $value);
        return $this->builder->whereRaw('LOWER(experience) LIKE LOWER(?)', [$searchTerm]);
    }

    public function createdAt($value)
    {
        $dates = explode(',', $value);
        if (count($dates) > 1) {
            // Aseguramos que las fechas sean tratadas correctamente
            return $this->builder->whereDate('created_at', '>=', $dates[0])
                                ->whereDate('created_at', '<=', $dates[1]);
        } else {
            return $this->builder->whereDate('created_at', $value);
        }
    }

    public function updatedAt($value)
    {
        $dates = explode(',', $value);
        return count($dates) > 1
            ? $this->builder->whereBetween('updated_at', $dates)
            : $this->builder->whereDate('updated_at', $value);
    }
}