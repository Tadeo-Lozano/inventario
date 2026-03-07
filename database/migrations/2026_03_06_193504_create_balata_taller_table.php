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
        if (Schema::hasTable('balata_taller')) {
            return;
        }

        Schema::create('balata_taller', function (Blueprint $table) {
            $table->foreignId('balata_id')->constrained()->cascadeOnDelete();
            $table->foreignId('taller_id')->constrained('talleres')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['balata_id', 'taller_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balata_taller');
    }
};
