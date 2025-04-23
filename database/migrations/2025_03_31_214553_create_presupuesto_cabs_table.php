<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresupuestoCabsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('presupuestos_cab', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->date('fecha');
            $table->string('nombre');
            $table->date('fecha_nac')->nullable();
            $table->string('dni', 20)->nullable();
            $table->integer('centro_id');
            $table->integer('profesional_id')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('restrict');
            $table->decimal('valor_dolar', 10, 2)->default(0);
            $table->string('estado', 1)->default('I'); // I: ingresado, P:pagado, S:saldado
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->softDeletes();

            $table->foreign('centro_id')->references('id')->on('centros')->onDelete('restrict');
            $table->foreign('profesional_id')->references('id')->on('profesionales')->onDelete('restrict');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('presupuesto_cabs');
    }
}
