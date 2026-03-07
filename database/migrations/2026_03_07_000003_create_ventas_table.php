<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ventas')) {
            return;
        }

        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balata_id')->nullable()->constrained('balatas')->nullOnDelete();
            $table->string('codigo_balata', 80);
            $table->string('marca_balata', 80)->nullable();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_inventario_unitario', 10, 2);
            $table->decimal('precio_venta_unitario', 10, 2);
            $table->date('fecha_venta')->index();
            $table->text('nota')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
