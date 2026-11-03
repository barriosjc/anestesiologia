<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ordenes_pagos_audit_cab')) {
            return;
        }

        Schema::table('ordenes_pagos_audit_cab', function (Blueprint $table) {
            if (! Schema::hasColumn('ordenes_pagos_audit_cab', 'documento_llamaparse_id')) {
                $table->unsignedInteger('documento_llamaparse_id')->nullable()->after('id');
                $table->unique('documento_llamaparse_id');
                $table->foreign('documento_llamaparse_id')
                    ->references('id')
                    ->on('documentos_llamaparse')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('ordenes_pagos_audit_cab', 'nro_liquidacion')) {
                $table->string('nro_liquidacion', 50)->nullable()->after('nro_op');
            }

            if (! Schema::hasColumn('ordenes_pagos_audit_cab', 'total_sistema')) {
                $table->decimal('total_sistema', 14, 2)->default(0)->after('op_total');
            }

            if (! Schema::hasColumn('ordenes_pagos_audit_cab', 'total_diferencia')) {
                $table->decimal('total_diferencia', 14, 2)->default(0)->after('total_sistema');
            }

            if (! Schema::hasColumn('ordenes_pagos_audit_cab', 'cantidad_diferencias')) {
                $table->unsignedInteger('cantidad_diferencias')->default(0)->after('total_diferencia');
            }

            if (! Schema::hasColumn('ordenes_pagos_audit_cab', 'auditado_at')) {
                $table->timestamp('auditado_at')->nullable()->after('cantidad_diferencias');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ordenes_pagos_audit_cab')) {
            return;
        }

        Schema::table('ordenes_pagos_audit_cab', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes_pagos_audit_cab', 'documento_llamaparse_id')) {
                $table->dropForeign(['documento_llamaparse_id']);
                $table->dropUnique(['documento_llamaparse_id']);
                $table->dropColumn('documento_llamaparse_id');
            }

            foreach (['nro_liquidacion', 'total_sistema', 'total_diferencia', 'cantidad_diferencias', 'auditado_at'] as $columna) {
                if (Schema::hasColumn('ordenes_pagos_audit_cab', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
