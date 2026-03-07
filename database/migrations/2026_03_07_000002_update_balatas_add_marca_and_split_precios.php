<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('balatas')) {
            return;
        }

        $hasMarca = Schema::hasColumn('balatas', 'marca');
        $hasPrecioInventario = Schema::hasColumn('balatas', 'precio_inventario');
        $hasPrecioVenta = Schema::hasColumn('balatas', 'precio_venta');

        Schema::table('balatas', function (Blueprint $table) use ($hasMarca, $hasPrecioInventario, $hasPrecioVenta) {
            if (! $hasMarca) {
                $table->string('marca')->nullable();
            }

            if (! $hasPrecioInventario) {
                $table->decimal('precio_inventario', 10, 2)->nullable();
            }

            if (! $hasPrecioVenta) {
                $table->decimal('precio_venta', 10, 2)->nullable();
            }
        });

        DB::table('balatas')
            ->whereNull('marca')
            ->update(['marca' => 'Sin marca']);

        DB::table('balatas')
            ->where('marca', '')
            ->update(['marca' => 'Sin marca']);

        if (Schema::hasColumn('balatas', 'precio')) {
            DB::table('balatas')
                ->whereNull('precio_inventario')
                ->update(['precio_inventario' => DB::raw('precio')]);

            DB::table('balatas')
                ->whereNull('precio_venta')
                ->update(['precio_venta' => DB::raw('precio')]);
        }

        DB::table('balatas')
            ->whereNull('precio_inventario')
            ->update(['precio_inventario' => 0]);

        DB::table('balatas')
            ->whereNull('precio_venta')
            ->update(['precio_venta' => 0]);

        $columnsToDrop = [];
        if (Schema::hasColumn('balatas', 'anio_inicio')) {
            $columnsToDrop[] = 'anio_inicio';
        }
        if (Schema::hasColumn('balatas', 'anio_fin')) {
            $columnsToDrop[] = 'anio_fin';
        }
        if (Schema::hasColumn('balatas', 'precio')) {
            $columnsToDrop[] = 'precio';
        }

        if ($columnsToDrop !== []) {
            Schema::table('balatas', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('balatas')) {
            return;
        }

        $hasAnioInicio = Schema::hasColumn('balatas', 'anio_inicio');
        $hasAnioFin = Schema::hasColumn('balatas', 'anio_fin');
        $hasPrecio = Schema::hasColumn('balatas', 'precio');

        Schema::table('balatas', function (Blueprint $table) use ($hasAnioInicio, $hasAnioFin, $hasPrecio) {
            if (! $hasAnioInicio) {
                $table->unsignedSmallInteger('anio_inicio')->nullable();
            }

            if (! $hasAnioFin) {
                $table->unsignedSmallInteger('anio_fin')->nullable();
            }

            if (! $hasPrecio) {
                $table->decimal('precio', 10, 2)->nullable();
            }
        });

        if (Schema::hasColumn('balatas', 'precio') && Schema::hasColumn('balatas', 'precio_inventario')) {
            DB::table('balatas')
                ->whereNull('precio')
                ->update(['precio' => DB::raw('precio_inventario')]);
        }

        $columnsToDrop = [];
        if (Schema::hasColumn('balatas', 'marca')) {
            $columnsToDrop[] = 'marca';
        }
        if (Schema::hasColumn('balatas', 'precio_inventario')) {
            $columnsToDrop[] = 'precio_inventario';
        }
        if (Schema::hasColumn('balatas', 'precio_venta')) {
            $columnsToDrop[] = 'precio_venta';
        }

        if ($columnsToDrop !== []) {
            Schema::table('balatas', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
