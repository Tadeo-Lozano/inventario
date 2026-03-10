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
        if (! Schema::hasTable('talleres')) {
            return;
        }

        Schema::table('talleres', function (Blueprint $table) {
            if (! Schema::hasColumn('talleres', 'calle')) {
                $table->string('calle', 150)->default('')->after('nombre');
            }

            if (! Schema::hasColumn('talleres', 'colonia')) {
                $table->string('colonia', 150)->default('')->after('calle');
            }

            if (! Schema::hasColumn('talleres', 'numero')) {
                $table->string('numero', 30)->default('')->after('colonia');
            }

            if (! Schema::hasColumn('talleres', 'telefono')) {
                $table->string('telefono', 30)->default('')->after('numero');
            }
        });

        if (Schema::hasColumn('talleres', 'ubicacion') && Schema::hasColumn('talleres', 'calle')) {
            DB::table('talleres')
                ->where('calle', '')
                ->whereNotNull('ubicacion')
                ->where('ubicacion', '<>', '')
                ->update(['calle' => DB::raw('ubicacion')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('talleres')) {
            return;
        }

        $columns = array_filter([
            Schema::hasColumn('talleres', 'calle') ? 'calle' : null,
            Schema::hasColumn('talleres', 'colonia') ? 'colonia' : null,
            Schema::hasColumn('talleres', 'numero') ? 'numero' : null,
            Schema::hasColumn('talleres', 'telefono') ? 'telefono' : null,
        ]);

        if ($columns === []) {
            return;
        }

        Schema::table('talleres', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
