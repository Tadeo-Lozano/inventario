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
        if (! Schema::hasTable('balatas') || Schema::hasColumn('balatas', 'posicion')) {
            return;
        }

        Schema::table('balatas', function (Blueprint $table) {
            $table->string('posicion', 20)->default('Delantera')->after('calidad');
        });

        DB::table('balatas')
            ->whereNull('posicion')
            ->update(['posicion' => 'Delantera']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('balatas') || ! Schema::hasColumn('balatas', 'posicion')) {
            return;
        }

        Schema::table('balatas', function (Blueprint $table) {
            $table->dropColumn('posicion');
        });
    }
};
