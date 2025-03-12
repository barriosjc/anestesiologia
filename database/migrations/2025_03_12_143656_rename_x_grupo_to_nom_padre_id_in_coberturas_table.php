<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameXGrupoToNomPadreIdInCoberturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coberturas', function (Blueprint $table) {
            $table->renameColumn('x_grupo', 'nom_padre_id');
        });

        // Actualizar todos los registros existentes con el valor 1
        DB::table('coberturas')->update(['nom_padre_id' => 1]);
        DB::table('coberturas')->where('id', 25)->update(['nom_padre_id' => 2]);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coberturas', function (Blueprint $table) {
            $table->renameColumn('nom_padre_id', 'x_grupo');
        });
    }
}
