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
        if (Schema::hasTable('balatas')) {
            return;
        }

        Schema::create('balatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarima_id')->constrained()->restrictOnDelete();
            $table->string('codigo')->unique();
            $table->string('marca');
            $table->string('calidad');
            $table->text('vehiculos');
            $table->unsignedInteger('cantidad')->default(0);
            $table->decimal('precio_inventario', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balatas');
    }
};
