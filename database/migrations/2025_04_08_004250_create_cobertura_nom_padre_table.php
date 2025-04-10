<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoberturaNomPadreTable extends Migration
{
    public function up()
    {
        Schema::create('cobertura_nom_padre', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Motor InnoDB al inicio

            // Definir columnas
            $table->integer('cobertura_id');
            $table->unsignedBigInteger('nom_padre_id');

            // Claves foráneas
            $table->foreign('cobertura_id')->references('id')->on('coberturas')->onDelete('cascade');
            $table->foreign('nom_padre_id')->references('id')->on('nom_padres')->onDelete('cascade');

            // Clave primaria compuesta
            $table->primary(['cobertura_id', 'nom_padre_id']);

            $table->timestamps(); // Opcional
        });
    }

    public function down()
    {
        Schema::dropIfExists('cobertura_nom_padre');
    }
}