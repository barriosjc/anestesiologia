<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNomPadreIdToNomencladorTable extends Migration
{
    public function up()
    {
        Schema::table('nomenclador', function (Blueprint $table) {
            $table->bigInteger('nom_padre_id')->unsigned()->nullable()->after('id');
        });

        // Actualizar todos los registros existentes con el valor 1
        DB::table('nomenclador')->update(['nom_padre_id' => 1]);
    }

    public function down()
    {
        Schema::table('nomenclador', function (Blueprint $table) {
            $table->dropColumn('nom_padre_id');
        });
    }
}
