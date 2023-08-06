<?php
namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{

    public function getCreatedAtAttribute($value)
    {
        $resu = '';
        if (!empty($value)) {
            $resu = date('d/m/Y', strtotime($value));
        }

        return $resu;
    }

    public static function v_roles_empresas ($empresas_id) {

        $resu = Role::query()
        ->select(['roles.id', 'roles.name', 'roles.guard_name'])
        ->where('guard_name', session('empresa')->uri)
        ->orderby('roles.name', 'desc');
  
        return $resu;
    }

}
