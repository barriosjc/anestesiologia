<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La relacion entre los PDFs y las coberturas se hace solo por cod_uom.
        if (! Schema::hasTable('coberturas') || ! Schema::hasColumn('coberturas', 'id_uom')) {
            return;
        }

        Schema::table('coberturas', function (Blueprint $table) {
            $table->dropColumn('id_uom');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('coberturas') || Schema::hasColumn('coberturas', 'id_uom')) {
            return;
        }

        Schema::table('coberturas', function (Blueprint $table) {
            $table->string('id_uom', 20)->nullable()->after('sigla')->index();
        });
    }
};
