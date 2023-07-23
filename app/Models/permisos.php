<?php
namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class permisos extends SpatiePermission
{

    public function getCreatedAtAttribute($value)
    {
        $resu = '';
        if (!empty($value)) {
            $resu = date('d/m/Y', strtotime($value));
        }

        return $resu;
    }

}


