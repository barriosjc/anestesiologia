<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresupuestoDetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('presupuestos_det', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('presupuesto_cab_id');
            $table->integer('cobertura_id');
            $table->string('nivel');
            $table->string('codigo');
            $table->decimal('porcentaje', 5, 2);
            $table->decimal('valor', 10, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';

            $table->foreign('presupuesto_cab_id')->references('id')->on('presupuestos_cab')->onDelete('restrict');
            $table->foreign('cobertura_id')->references('id')->on('coberturas')->onDelete('restrict');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('presupuesto_dets');
    }
}
