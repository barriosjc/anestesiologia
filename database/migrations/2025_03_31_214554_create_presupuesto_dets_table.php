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
            $table->id();
            $table->foreignId('presupuesto_cab_id')->constrained('presupuestos_cab')->onDelete('cascade'); // Relación con presupuestos_cab
            $table->foreignId('cobertura_id')->constrained('coberturas')->onDelete('cascade');
            $table->string('nivel');
            $table->string('codigo');
            $table->decimal('porcentaje', 5, 2);
            $table->decimal('valor', 10, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
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
