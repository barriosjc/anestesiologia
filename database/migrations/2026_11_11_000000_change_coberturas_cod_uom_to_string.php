<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // La tabla coberturas es preexistente (no tiene migracion de creacion).
        // El campo cod_uom pasa de entero a texto para admitir varios códigos
        // UOM separados por coma ("84,274").
        if (! Schema::hasTable('coberturas') || ! Schema::hasColumn('coberturas', 'cod_uom')) {
            return;
        }

        Schema::table('coberturas', function (Blueprint $table) {
            $table->string('cod_uom', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        // No se revierte: convertir de vuelta a entero perderia los códigos
        // múltiples que ya se hubieran cargado.
    }
};
