<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Taller extends Model
{
    protected $table = 'talleres';

    protected $fillable = [
        'nombre',
        'ubicacion',
    ];

    public function balatas(): BelongsToMany
    {
        return $this->belongsToMany(Balata::class)->withTimestamps();
    }
}
