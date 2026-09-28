<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terceros_anestesia', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('paciente', 255);
            $table->integer('nomenclador_id')->nullable();
            $table->string('estado', 50)->default('realizado');
            $table->integer('profesional_id')->nullable();
            $table->integer('cobertura_id')->nullable();
            $table->boolean('urgencia')->default(false);
            $table->text('observaciones')->nullable();
            $table->boolean('pasado_sistema')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';

            $table->foreign('nomenclador_id')->references('id')->on('nomenclador')->onDelete('set null');
            $table->foreign('profesional_id')->references('id')->on('profesionales')->onDelete('set null');
            $table->foreign('cobertura_id')->references('id')->on('coberturas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terceros_anestesia');
    }
};
