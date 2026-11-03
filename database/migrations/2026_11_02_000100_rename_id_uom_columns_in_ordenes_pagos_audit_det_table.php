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

        Schema::table('ordenes_pagos_audit_det', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes_pagos_audit_det', 'id_uom_archivo')) {
                $table->renameColumn('id_uom_archivo', 'cod_uom_archivo');
            }

            if (Schema::hasColumn('ordenes_pagos_audit_det', 'id_uom_sistema')) {
                $table->renameColumn('id_uom_sistema', 'cod_uom_sistema');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ordenes_pagos_audit_det')) {
            return;
        }

        Schema::table('ordenes_pagos_audit_det', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes_pagos_audit_det', 'cod_uom_archivo')) {
                $table->renameColumn('cod_uom_archivo', 'id_uom_archivo');
            }

            if (Schema::hasColumn('ordenes_pagos_audit_det', 'cod_uom_sistema')) {
                $table->renameColumn('cod_uom_sistema', 'id_uom_sistema');
            }
        });
    }
};
