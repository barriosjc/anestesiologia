<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('terceros_anestesia', function (Blueprint $table) {
            // Permite almacenar de qué nomenclador padre proviene el
            // procedimiento, para poder distinguir si nomenclador_id
            // apunta a 'nomenclador' o a 'nom_practicas_estudios'.
            $table->unsignedInteger('nom_padre_id')->nullable()->after('nomenclador_id');

            // Se elimina la FK sobre nomenclador_id porque ahora el
            // valor puede provenir también de 'nom_practicas_estudios',
            // que es otra tabla con su propio autoincremental.
            $table->dropForeign(['nomenclador_id']);
        });
    }

    public function down(): void
    {
        Schema::table('terceros_anestesia', function (Blueprint $table) {
            $table->foreign('nomenclador_id')
                ->references('id')->on('nomenclador')
                ->onDelete('set null');
            $table->dropColumn('nom_padre_id');
        });
    }
};
