<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las retenciones de la orden de pago no vienen en el detalle: estan en la
 * hoja "Valores" del XLSX (Caja de Prof. de la salud, Gastos Administrativos
 * e Ingresos Brutos). Se guardan como snapshot en la cabecera, igual que
 * op_total o total_sistema.
 *
 * - retencion_caja   = fila "Caja de Prof. de la salud".
 * - retencion_gastos = filas "Gastos Administrativos 11%" + "Gastos Administrativos 5%".
 * - op_retenciones   = retencion de Ingresos Brutos (IB).
 *
 * Las dos columnas nuevas son NULL cuando el archivo no informa el dato, para
 * poder distinguir "sin dato" de "cero".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes_pagos_audit_cab', function (Blueprint $table) {
            $table->decimal('retencion_caja', 14, 2)->nullable()->after('op_total');
            $table->decimal('retencion_gastos', 14, 2)->nullable()->after('retencion_caja');
        });
    }

    public function down(): void
    {
        Schema::table('ordenes_pagos_audit_cab', function (Blueprint $table) {
            $table->dropColumn(['retencion_caja', 'retencion_gastos']);
        });
    }
};
