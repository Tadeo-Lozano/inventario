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
        if (Schema::hasTable('balata_imagenes')) {
            return;
        }

        Schema::create('balata_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balata_id')->constrained()->cascadeOnDelete();
            $table->string('ruta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balata_imagenes');
    }
};
