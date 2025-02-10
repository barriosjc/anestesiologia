<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateValorToDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consumos_det', function (Blueprint $table) {
            // Crear el nuevo campo valor2
            $table->decimal('valor2', 10, 2)->nullable();
        });

        // Copiar los datos del campo valor al campo valor2
        DB::statement('UPDATE consumos_det SET valor2 = valor');

        Schema::table('consumos_det', function (Blueprint $table) {
            // Eliminar el campo valor antiguo
            $table->dropColumn('valor');
        });

        Schema::table('consumos_det', function (Blueprint $table) {
            // Renombrar el campo valor2 a valor
            $table->renameColumn('valor2', 'valor');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consumos_det', function (Blueprint $table) {
            // Crear el campo valor antiguo
            $table->float('valor')->nullable();
        });

        // Copiar los datos del campo valor al campo valor antiguo
        DB::statement('UPDATE consumos_det SET valor = valor2');

        Schema::table('consumos_det', function (Blueprint $table) {
            // Eliminar el campo valor2
            $table->dropColumn('valor2');
        });
    }
}