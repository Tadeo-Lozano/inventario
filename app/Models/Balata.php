<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Balata extends Model
{
    protected $fillable = [
        'tarima_id',
        'codigo',
        'marca',
        'calidad',
        'posicion',
        'vehiculos',
        'cantidad',
        'precio_inventario',
        'precio_venta',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_inventario' => 'decimal:2',
            'precio_venta' => 'decimal:2',
        ];
    }

    public function tarima(): BelongsTo
    {
        return $this->belongsTo(Tarima::class);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(BalataImagen::class);
    }

    public function talleres(): BelongsToMany
    {
        return $this->belongsToMany(Taller::class)->withTimestamps();
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }
}
