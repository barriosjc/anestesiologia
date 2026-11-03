<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La tabla coberturas es preexistente en el sistema y no tiene migracion.
        if (! Schema::hasTable('coberturas') || Schema::hasColumn('coberturas', 'id_uom')) {
            return;
        }

        Schema::table('coberturas', function (Blueprint $table) {
            $table->string('id_uom', 20)->nullable()->after('sigla')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('coberturas') || ! Schema::hasColumn('coberturas', 'id_uom')) {
            return;
        }

        Schema::table('coberturas', function (Blueprint $table) {
            $table->dropColumn('id_uom');
        });
    }
};
