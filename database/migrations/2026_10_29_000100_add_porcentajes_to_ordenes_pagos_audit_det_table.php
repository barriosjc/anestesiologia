<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ordenes_pagos_audit_det')) {
            return;
        }

        $agregarCodigoSistema = ! Schema::hasColumn('ordenes_pagos_audit_det', 'codigo_sistema');
        $agregarPorcentajeArchivo = ! Schema::hasColumn('ordenes_pagos_audit_det', 'porcentaje_archivo');
        $agregarPorcentajeSistema = ! Schema::hasColumn('ordenes_pagos_audit_det', 'porcentaje_sistema');

        Schema::table('ordenes_pagos_audit_det', function (Blueprint $table) use (
            $agregarCodigoSistema,
            $agregarPorcentajeArchivo,
            $agregarPorcentajeSistema
        ) {
            if ($agregarCodigoSistema) {
                $table->string('codigo_sistema', 20)->nullable()->after('nivel_sistema');
            }

            if ($agregarPorcentajeArchivo) {
                $table->decimal('porcentaje_archivo', 6, 2)->nullable()->after('porcentaje_diferencia');
            }

            if ($agregarPorcentajeSistema) {
                $table->decimal('porcentaje_sistema', 6, 2)->nullable()->after('porcentaje_archivo');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ordenes_pagos_audit_det')) {
            return;
        }

        $columnas = array_values(array_filter(
            ['porcentaje_sistema', 'porcentaje_archivo', 'codigo_sistema'],
            fn (string $columna) => Schema::hasColumn('ordenes_pagos_audit_det', $columna)
        ));

        if ($columnas === []) {
            return;
        }

        Schema::table('ordenes_pagos_audit_det', function (Blueprint $table) use ($columnas) {
            $table->dropColumn($columnas);
        });
    }
};
