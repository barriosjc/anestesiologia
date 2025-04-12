<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerenciadoraNomPadre extends Migration
{
    public function up()
    {
        Schema::create('gerenciadora_nom_padre', function (Blueprint $table) {
            $table->id();

            // gerenciadora_id como unsignedInteger
            $table->Integer('gerenciadora_id');
            $table->foreign('gerenciadora_id')
                  ->references('id')
                  ->on('gerenciadoras')
                  ->onDelete('restrict');

            // nom_padre_id como unsignedBigInteger
            $table->unsignedBigInteger('nom_padre_id');
            $table->foreign('nom_padre_id')
                  ->references('id')
                  ->on('nom_padres')
                  ->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gerenciadora_nom_padre');
    }
}
