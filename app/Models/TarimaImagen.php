<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarimaImagen extends Model
{
    protected $table = 'tarima_imagenes';

    protected $fillable = [
        'tarima_id',
        'ruta',
    ];

    public function tarima(): BelongsTo
    {
        return $this->belongsTo(Tarima::class);
    }
}
