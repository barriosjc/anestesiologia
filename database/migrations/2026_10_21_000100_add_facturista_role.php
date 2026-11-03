<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Perfil requerido para el acceso al modulo Ordenes Pagos Audit.
        Role::findOrCreate('facturista', 'web');
    }

    public function down(): void
    {
        Role::where('name', 'facturista')
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
