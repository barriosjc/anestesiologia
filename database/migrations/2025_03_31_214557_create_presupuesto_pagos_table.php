<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresupuestoPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
        */
    public function up()
    {
        Schema::create('presupuestos_pagos', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('presupuesto_cab_id');
            $table->date('fecha');
            $table->decimal('valor', 10, 2);
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->softDeletes();
            $table->timestamps();
            $table->engine = 'InnoDB';

            $table->foreign('presupuesto_cab_id')->references('id')->on('presupuestos_cab')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('presupuesto_pagos');
    }
}
