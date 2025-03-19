<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNomPracticasEstudiosTable extends Migration
{
    public function up()
    {
        Schema::create('nom_practicas_estudios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nom_padre_id');
            $table->string('nombre', 200);
            $table->string('codigo', 10)->unique();
            $table->softDeletes();
            $table->timestamps();
            $table->engine = 'InnoDB';

            // Relación con la tabla nom_padre
            $table->foreign('nom_padre_id')->references('id')->on('nom_padres')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nom_practicas_estudios');
    }
}