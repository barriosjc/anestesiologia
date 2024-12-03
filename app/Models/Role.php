<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{

    protected $casts = [
        'created_at' => 'date',
    ];

    public function getCreatedAtAttribute($value)
    {
        $resu = '';
        if (!empty($value)) {
            $resu = date('d/m/Y', strtotime($value));
        }

        return $resu;
    }

    public static function v_roles () {

        $resu = Role::query()
        ->select(['roles.id', 'roles.name', 'roles.guard_name'])
        ->orderby('roles.name', 'desc');
  
        return $resu;
    }

}
