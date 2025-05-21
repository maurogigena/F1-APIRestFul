<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'id',
        'number',
        'name',
        'team_id',
        'age',
        'country',
        'experience',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
