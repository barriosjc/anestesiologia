<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('documentos_ocr');
        Schema::dropIfExists('documentos_xlsx');
        Schema::dropIfExists('documentos_ia');

        if (Schema::hasTable('permissions')) {
            $permisoId = DB::table('permissions')
                ->where('name', 'adm_documentos_ia')
                ->where('guard_name', 'web')
                ->value('id');

            if ($permisoId !== null) {
                if (Schema::hasTable('role_has_permissions')) {
                    DB::table('role_has_permissions')->where('permission_id', $permisoId)->delete();
                }

                if (Schema::hasTable('model_has_permissions')) {
                    DB::table('model_has_permissions')->where('permission_id', $permisoId)->delete();
                }

                DB::table('permissions')->where('id', $permisoId)->delete();
            }
        }
    }

    public function down(): void
    {
        // Modulos retirados: no se recrean.
    }
};
