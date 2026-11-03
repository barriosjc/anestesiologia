<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ordenes_pagos_audit_det')) {
            return;
        }

        Schema::create('ordenes_pagos_audit_det', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('orden_pago_audit_cab_id');
            $table->unsignedInteger('parte_cab_id')->nullable();
            $table->unsignedInteger('consumo_det_id')->nullable();
            $table->unsignedInteger('cobertura_id')->nullable();
            // `niveles` es una tabla preexistente MyISAM con id INT con signo:
            // no admite FK. Se deja solo el indice.
            $table->integer('nivel_id')->nullable();

            $table->string('atencion', 20)->nullable();
            $table->date('fecha_archivo')->nullable();
            $table->date('fecha_sistema')->nullable();
            $table->string('obra_social_archivo', 120)->nullable();
            $table->string('id_uom_archivo', 20)->nullable();
            $table->string('id_uom_sistema', 20)->nullable();
            $table->string('periodo_archivo', 10)->nullable();
            $table->string('codigo_archivo', 20)->nullable();
            $table->string('nivel_sistema', 20)->nullable();
            $table->string('descripcion_archivo', 200)->nullable();
            $table->string('descripcion_sistema', 200)->nullable();

            $table->decimal('valor_archivo', 14, 2)->default(0);
            $table->decimal('valor_sistema', 14, 2)->default(0);
            $table->decimal('diferencia', 14, 2)->default(0);
            $table->decimal('porcentaje_diferencia', 8, 2)->default(0);

            $table->string('severidad', 20)->default('sin-diferencia');
            $table->string('estado', 30)->default('ok');

            $table->boolean('dif_fecha')->default(false);
            $table->boolean('dif_obra_social')->default(false);
            $table->boolean('dif_descripcion')->default(false);

            $table->timestamps();

            $table->index('atencion');
            $table->index('parte_cab_id');
            $table->index('consumo_det_id');
            $table->index('cobertura_id');
            $table->index('nivel_id');
            $table->index('nivel_sistema');
            $table->index(['orden_pago_audit_cab_id', 'severidad']);
            $table->index(['orden_pago_audit_cab_id', 'estado']);

            $table->foreign('orden_pago_audit_cab_id')
                ->references('id')
                ->on('ordenes_pagos_audit_cab')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenes_pagos_audit_det');
    }
};
