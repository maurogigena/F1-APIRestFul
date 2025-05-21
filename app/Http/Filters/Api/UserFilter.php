<?php

namespace App\Http\Filters\Api;

use App\Http\Filters\Api\QueryFilter;

class UserFilter extends QueryFilter
{
    protected $sortable = [
        'id',
        'isAdmin',
        'name',
        'email',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function id($value)
    {
        return $this->builder->where('id', $value);
    }

    public function isAdmin($value)
    {
        return $this->builder->where('is_admin', $value);
    }

    public function name($value)
    {
        return $this->builder->where('name', 'like', str_replace('*', '%', $value));
    }

    public function email($value)
    {
        return $this->builder->where('email', $value);
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