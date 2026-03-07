<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Tarima extends Model
{
    protected $fillable = [
        'numero_identificacion',
        'foto',
    ];

    public function balatas(): HasMany
    {
        return $this->hasMany(Balata::class);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(TarimaImagen::class);
    }
}
