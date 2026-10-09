<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('terceros_anestesia', function (Blueprint $table) {
            // DNI del paciente: opcional y de hasta 10 caracteres.
            $table->string('dni', 10)->nullable()->after('paciente');
        });
    }

    public function down(): void
    {
        Schema::table('terceros_anestesia', function (Blueprint $table) {
            $table->dropColumn('dni');
        });
    }
};
