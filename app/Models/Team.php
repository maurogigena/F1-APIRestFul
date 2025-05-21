<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
    ];

    // Relación 1:N: un team tiene muchos drivers
    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }
}