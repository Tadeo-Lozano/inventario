<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class BalataImagen extends Model
{
    protected $table = 'balata_imagenes';

    protected $fillable = [
        'balata_id',
        'ruta',
    ];

    public function balata(): BelongsTo
    {
        return $this->belongsTo(Balata::class);
    }
}
