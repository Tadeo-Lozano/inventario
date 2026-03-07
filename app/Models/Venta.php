<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'balata_id',
        'codigo_balata',
        'marca_balata',
        'cantidad',
        'precio_inventario_unitario',
        'precio_venta_unitario',
        'fecha_venta',
        'nota',
    ];

    protected function casts(): array
    {
        return [
            'fecha_venta' => 'date',
            'cantidad' => 'integer',
            'precio_inventario_unitario' => 'decimal:2',
            'precio_venta_unitario' => 'decimal:2',
        ];
    }

    public function balata(): BelongsTo
    {
        return $this->belongsTo(Balata::class);
    }
}
