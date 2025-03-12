<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToNomencladorTable extends Migration
{
    public function up()
    {
        Schema::table('nomenclador', function (Blueprint $table) {
            $table->foreign('nom_padre_id')
                  ->references('id')
                  ->on('nom_padres')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('nomenclador', function (Blueprint $table) {
            $table->dropForeign(['nom_padre_id']);
        });
    }
}