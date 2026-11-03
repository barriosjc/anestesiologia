<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenes_pagos_audit_cab', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nro_op', 20);
            $table->integer('profesional_id')->nullable();
            $table->date('fecha')->nullable();
            $table->decimal('op_total', 14, 2)->default(0);
            $table->decimal('op_retenciones', 14, 2)->default(0);
            $table->timestamps();

            $table->unique('nro_op');
            $table->index('profesional_id');
            $table->index('fecha');

            $table->foreign('profesional_id')
                ->references('id')
                ->on('profesionales')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_pagos_audit_cab');
    }
};
