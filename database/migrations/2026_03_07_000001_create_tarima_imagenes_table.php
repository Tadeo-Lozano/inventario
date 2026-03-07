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
        if (! Schema::hasTable('tarima_imagenes')) {
            Schema::create('tarima_imagenes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tarima_id')->constrained()->cascadeOnDelete();
                $table->string('ruta');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tarimas') || ! Schema::hasColumn('tarimas', 'foto')) {
            return;
        }

        DB::table('tarimas')
            ->select(['id', 'foto', 'created_at', 'updated_at'])
            ->whereNotNull('foto')
            ->where('foto', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($tarimas): void {
                $inserts = [];

                foreach ($tarimas as $tarima) {
                    $inserts[] = [
                        'tarima_id' => $tarima->id,
                        'ruta' => $tarima->foto,
                        'created_at' => $tarima->created_at,
                        'updated_at' => $tarima->updated_at,
                    ];
                }

                if (! empty($inserts)) {
                    DB::table('tarima_imagenes')->insert($inserts);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarima_imagenes');
    }
};
