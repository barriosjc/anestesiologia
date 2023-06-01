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

    public static function v_roles (int $empresas_id) {

        $resu = Role::query()
        ->select(['roles.id', 'roles.name', 'roles.guard_name'])
        ->join('roles_empresas as re', 're.roles_id', 'roles.id')
        ->where('re.empresas_id', $empresas_id)
        ->orderby('roles.name', 'desc');
// dd("tabla encuesta",$resu);
        return $resu;
    }

}
